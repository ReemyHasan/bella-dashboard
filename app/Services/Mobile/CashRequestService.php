<?php

namespace App\Services\Mobile;

use App\Enums\CashRequestStatus;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\Address;
use App\Models\AppUser;
use App\Models\CashRequest;
use App\Models\Currency;
use App\Models\PaymentMethod;
use App\Models\Vault;
use App\Services\Shared\CashRequestSharedService;
use App\Traits\HandlesImageUpload;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;


class CashRequestService
{
    use HandlesImageUpload;

    public function __construct(private CashRequestSharedService $cashRequestSharedService) {}

    public function list($request)
    {
        $user = auth()->user();

        return CashRequest::query()->with('fromVault.owner', 'requestedFor', 'currency', 'paymentMethod', 'requestedBy', 'address')
            ->where(function ($q) use ($user) {
                if ($user->hasRole('Team Manager')) {

                    $q->whereHasMorph(
                        'requestedFor',
                        [AppUser::class],
                        function ($sub) use ($user) {

                            $sub->where('team_id', $user->team_id);
                        }
                    );
                } elseif ($user->hasRole('Team Leader')) {

                    $q->whereHasMorph(
                        'requestedFor',
                        [AppUser::class],
                        function ($sub) use ($user) {

                            $sub->where('subteam_id', $user->subteam_id);
                        }
                    );
                } else if ($user->is_warehouse_man) {
                    $q->where('delivered_by', $user->id);
                } else {

                    $q->where(function ($sub) use ($user) {

                        $sub

                            ->where(function ($x) use ($user) {

                                $x->where('requested_for_type', AppUser::class)
                                    ->where('requested_for_id', $user->id);
                            });
                    });
                }
            })
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {

            $address = Address::with('region.city.zone', 'region.warehouse.keeper')->find($data['address_id']);

            if (!$address) {
                throw new CustomException('العنوان غير موجود');
            }
            $region = $address->region;
            $warehouse = $address->region->warehouse;

            $fromVault = Vault::where('owner_id', $warehouse?->keeper_id)
                ->first();

            if (!$fromVault) {
                throw new CustomException('.الموزع ليس لديه خزنة');
            }

            $currency = Currency::findOrFail($data['currency_id']);

            $paymentMethod = PaymentMethod::findOrFail($data['payment_method_id']);

            $paymentMethodFields = $this->handlePaymentMethodFields(
                $data['payment_method_fields'] ?? [],
                $paymentMethod
            );
            $cashRequest = CashRequest::create([
                'from_vault_id' => $fromVault->id,
                'requested_amount' => $data['requested_amount'],
                'address_id' => $data['address_id'] ?? null,
                'address_details' => $data['address_details'] ?? null,
                'cash_request_reason' => $data['cash_request_reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                'status' => CashRequestStatus::PENDING->value,

                'requested_by_type' => get_class($user),
                'requested_by_id' => $user->id,

                'requested_for_type' => AppUser::class,
                'requested_for_id' => $user->id,
                'delivered_by' =>  $warehouse?->keeper_id,

                'delivery_cost' =>  $region->delivery_cost,

                'currency_id' =>  $data['currency_id'],
                'current_exchange_value' => $currency->exchange_value,
                'payment_method_id' =>  $data['payment_method_id'],
                'payment_method_fields' => $paymentMethodFields,


            ]);
            $cashRequest->load('fromVault.owner', 'requestedBy', 'currency', 'paymentMethod', 'address');

            return $cashRequest;
        });
    }

    public function handlePaymentMethodFields(array $fields, PaymentMethod $paymentMethod): array
    {
        $processedFields = [];

        foreach ($paymentMethod->required_fields as $field) {

            $key = $field['key'];
            $type = $field['type'] ?? 'text';

            if (!array_key_exists($key, $fields)) {
                continue;
            }

            $value = $fields[$key];

            if ($type === 'image' && $value instanceof \Illuminate\Http\UploadedFile) {

                $processedFields[$key] = $this->uploadImage($value, 'cash_requests');
            } else {

                $processedFields[$key] = $value;
            }
        }

        return $processedFields;
    }
    public function show(CashRequest $cashRequest)
    {
        $user = auth()->user();
        $requestedFor = $cashRequest->requestedFor;
        if ($user->hasRole('Team Manager')) {

            if (
                !$requestedFor ||
                $requestedFor->team_id != $user->team_id
            ) {
                throw new CustomException('غير مسموح بعرض الطلب');
            }
        } elseif ($user->hasRole('Team Leader')) {

            if (
                !$requestedFor ||
                $requestedFor->subteam_id != $user->subteam_id
            ) {
                throw new CustomException('غير مسموح بعرض الطلب');
            }
        } elseif ($user->hasRole('Warehouse Keeper')) {

            if (
                $cashRequest->delivered_by != $user->id
            ) {
                throw new CustomException('غير مسموح بعرض الطلب');
            }
        } else {

            $canView = (
                (
                    $cashRequest->requested_for_type
                    == AppUser::class
                    &&
                    $cashRequest->requested_for_id
                    == $user->id
                )
            );

            if (!$canView) {
                throw new CustomException('غير مسموح بعرض الطلب');
            }
        }

        $cashRequest->load('fromVault.owner', 'requestedFor', 'paymentMethod', 'requestedBy', 'currency', 'deliveredBy', 'paymentMethod', 'reviewedBy', 'address');
        return $cashRequest;
    }


    public function handle(CashRequest $cashRequest, array $data)
    {
        if ($cashRequest->delivered_by != auth()->user()->id) {
            throw new CustomException('لا يمكن معالجة الطلب إلا من قبل الموزع.');
        }
        $status = CashRequestStatus::from($data['status']);

        $this->changeStatus(
            $cashRequest,
            $status,
            $data['notes'] ?? null
        );
    }


    public function changeStatus(CashRequest $cashRequest, CashRequestStatus $status, ?string $notes = null)
    {
        $allowedTransitions = [
            CashRequestStatus::APPROVED->value => [
                CashRequestStatus::IN_TRANSIT->value,
                CashRequestStatus::DELIVERED->value,
                CashRequestStatus::NOT_DELIVERED->value,
                CashRequestStatus::CANCELLED->value

            ],
            CashRequestStatus::IN_TRANSIT->value => [
                CashRequestStatus::DELIVERED->value,
                CashRequestStatus::NOT_DELIVERED->value

            ],

            CashRequestStatus::DELIVERED->value => [
                CashRequestStatus::WAITING_DELIVERY_APPROVE->value,
                CashRequestStatus::COMPLETED->value

            ],

            CashRequestStatus::WAITING_DELIVERY_APPROVE->value => [
                CashRequestStatus::COMPLETED->value
            ],
        ];

        $current = $cashRequest->status;

        if (
            !isset($allowedTransitions[$current]) ||
            !in_array($status->value, $allowedTransitions[$current])
        ) {

            $from = CashRequestStatus::from($current)->label();
            $to   = CashRequestStatus::from($status->value)->label();

            throw new CustomException("تغيير الحالة غير مسموح من {$from} إلى {$to}");
        }

        return DB::transaction(function () use ($cashRequest, $status, $notes) {


            $updateData = [
                'status' => $status->value
            ];

            if ($notes) {
                $updateData['notes'] = $notes;
            }

            if ($status === CashRequestStatus::DELIVERED) {
                $updateData['delivered_at'] = now();
                // $this->addTransaction($cashRequest);
            }
            if ($status === CashRequestStatus::COMPLETED) {
                $this->cashRequestSharedService->transferFromUser($cashRequest);
                $this->cashRequestSharedService->addTransaction($cashRequest);
            }

            $cashRequest->update($updateData);
            return $cashRequest->refresh();
        });
    }
}
