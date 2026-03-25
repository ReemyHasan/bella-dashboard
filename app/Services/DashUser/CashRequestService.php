<?php

namespace App\Services\DashUser;

use App\Enums\CashRequestStatus;
use App\Enums\PaginationEnum;
use App\Enums\VaultTransactionType;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\CashRequest;
use App\Models\Currency;
use App\Models\DashUser;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CashRequestService
{
    public function list($request)
    {
        return CashRequest::with('fromVault.owner', 'requestedFor', 'currency', 'paymentMethod', 'requestedBy', 'address')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $user = Auth::user();

        return DB::transaction(function () use ($data, $user) {
            $currency = Currency::findOrFail($data['currency_id']);
            $cashRequest = CashRequest::create([
                'from_vault_id' => $data['from_vault_id'],
                'requested_amount' => $data['requested_amount'],
                'address_id' => $data['address_id'] ?? null,
                'address_details' => $data['address_details'] ?? null,
                'cash_request_reason' => $data['cash_request_reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                'status' => CashRequestStatus::PENDING->value,

                'requested_by_type' => get_class($user),
                'requested_by_id' => $user->id,

                'requested_for_type' => $data['requested_for_type'] == 'dash_user' ? DashUser::class : AppUser::class,
                'requested_for_id' => $data['requested_for_id'],


                'delivered_by' =>  $data['delivered_by'],

                'delivery_cost' =>  $data['delivery_cost'],
                // 'additional_delivery_cost' =>  $data['additional_delivery_cost'],

                'currency_id' =>  $data['currency_id'],
                'current_exchange_value' => $currency->exchange_value,
                'payment_method_id' =>  $data['payment_method_id'],

            ]);
            $cashRequest->load('fromVault.owner', 'requestedBy', 'currency', 'paymentMethod', 'address');

            return $cashRequest;
        });
    }

    public function update(CashRequest $cashRequest, array $data)
    {
        if ($cashRequest->status !== CashRequestStatus::PENDING->value) {
            throw new CustomException('لا يمكن تعديل الطلب بعد مراجعته.');
        }

        return DB::transaction(function () use ($cashRequest, $data) {
            $currency = Currency::findOrFail($data['currency_id']);

            $cashRequest->update([
                'from_vault_id' => $data['from_vault_id'],
                'requested_amount' => $data['requested_amount'],
                'address_id' => $data['address_id'] ?? null,
                'address_details' => $data['address_details'] ?? null,
                'cash_request_reason' => $data['cash_request_reason'] ?? null,
                'notes' => $data['notes'] ?? null,


                'delivered_by' =>  $data['delivered_by'],

                'delivery_cost' =>  $data['delivery_cost'],
                // 'additional_delivery_cost' =>  $data['additional_delivery_cost'],

                'currency_id' =>  $data['currency_id'],
                'current_exchange_value' => $currency->exchange_value,

                'payment_method_id' =>  $data['payment_method_id'],
            ]);
            $cashRequest->load('fromVault.owner', 'requestedBy', 'currency', 'paymentMethod', 'address');

            return $cashRequest;
        });
    }
    public function show(CashRequest $cashRequest)
    {
        $cashRequest->load('fromVault.owner', 'requestedFor', 'paymentMethod', 'requestedBy', 'currency', 'deliveredBy', 'paymentMethod', 'reviewedBy', 'address');
        return $cashRequest;
    }

    public function delete(CashRequest $cashRequest)
    {
        if (!in_array($cashRequest->status, [
            CashRequestStatus::PENDING->value,
            CashRequestStatus::REJECTED->value,
            CashRequestStatus::CANCELLED->value
        ])) {
            throw new CustomException('لا يمكن حذف الطلب بعد معالجته.');
        }
        return $cashRequest->delete();
    }

    public function handle(CashRequest $cashRequest, array $data)
    {
        $status = CashRequestStatus::from($data['status']);

        return match ($status) {
            CashRequestStatus::APPROVED =>
            $this->approve(
                $cashRequest,
                $data['approved_amount'],
                $data['delivered_by'] ?? null,
                $data['notes'] ?? null
            ),

            CashRequestStatus::REJECTED =>
            $this->reject(
                $cashRequest,
                $data['notes'] ?? null
            ),

            default =>
            $this->changeStatus(
                $cashRequest,
                $status,
                $data['delivered_by'] ?? null,
                $data['notes'] ?? null
            ),
        };
    }

    public function approve(CashRequest $cashRequest, float $approvedAmount, ?string $notes = null)
    {
        if ($cashRequest->status !== CashRequestStatus::PENDING->value) {
            throw new CustomException('لا يمكن الموافقة على الطلب.');
        }

        return DB::transaction(function () use ($cashRequest, $approvedAmount, $notes) {

            $cashRequest->update([
                'approved_amount' => $approvedAmount,
                'review_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'status' => CashRequestStatus::APPROVED->value,
            ]);

            return $cashRequest->refresh();
        });
    }
    public function reject(CashRequest $cashRequest, ?string $notes = null)
    {
        if ($cashRequest->status !== CashRequestStatus::PENDING->value) {
            throw new CustomException('لا يمكن رفض الطلب.');
        }

        return DB::transaction(function () use ($cashRequest, $notes) {

            $cashRequest->update([
                'review_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'status' => CashRequestStatus::REJECTED->value,
            ]);

            return $cashRequest->refresh();
        });
    }

    public function changeStatus(CashRequest $cashRequest, CashRequestStatus $status, ?int $deliveredBy = null, ?string $notes = null)
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
                CashRequestStatus::WAITING_DELIVERY_APPROVE->value
            ],

            // CashRequestStatus::WAITING_DELIVERY_APPROVE->value => [
            //     CashRequestStatus::COMPLETED->value
            // ],
        ];

        $current = $cashRequest->status;

        if (
            !isset($allowedTransitions[$current]) ||
            !in_array($status->value, $allowedTransitions[$current])
        ) {

            throw new CustomException('تغيير الحالة غير مسموح.');
        }

        return DB::transaction(function () use ($cashRequest, $status, $deliveredBy, $notes) {


            $updateData = [
                'status' => $status->value
            ];

            if ($deliveredBy) {
                $updateData['delivered_by'] = $deliveredBy;
            }

            if ($notes) {
                $updateData['notes'] = $notes;
            }

            if ($status === CashRequestStatus::DELIVERED) {
                $updateData['delivered_at'] = now();
            }

            $cashRequest->update($updateData);
            if ($status === CashRequestStatus::IN_TRANSIT) {
                $this->addTransaction($cashRequest);
            }

            return $cashRequest->refresh();
        });
    }
    private function addTransaction(CashRequest $cashRequest)
    {
        $user = Auth::user();

        $fromVault = $cashRequest->fromVault()->lockForUpdate()->first();


        $requestedAmount = $cashRequest->approved_amount;
        $exchangeValue = $cashRequest->current_exchange_value;

        $amount = $requestedAmount * $exchangeValue;

        if ($fromVault->balance < $amount) {
            throw new CustomException('الرصيد غير كافٍ في الخزنة المصدر.');
        }

        $fromBalanceBefore = $fromVault->balance;

        $fromBalanceAfter = $fromBalanceBefore - $amount;

        /**
         * Update vault balances
         */
        $fromVault->update([
            'balance' => $fromBalanceAfter
        ]);


        $currencyNote =
            "Requested: {$requestedAmount} {$cashRequest->currency->code} | " .
            "Exchange: {$exchangeValue} | " .
            "Vault Amount: {$amount}";

        VaultTransaction::create([
            'from_vault_id' => $fromVault->id,

            'type' => VaultTransactionType::CASH_REQUEST->value,

            'amount' => $amount,

            'transaction_date' => now(),

            'reason' => $cashRequest->cash_request_reason,
            'notes' => $currencyNote . ' | ' . ($cashRequest->notes ?? ''),

            'reference_type' => CashRequest::class,
            'reference_id' => $cashRequest->id,

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'from_vault_balance_before' => $fromBalanceBefore,
            'from_vault_balance_after' => $fromBalanceAfter,

        ]);
    }
}
