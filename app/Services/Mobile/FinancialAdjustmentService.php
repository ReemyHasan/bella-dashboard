<?php

namespace App\Services\Mobile;

use App\Enums\FinancialAdjustmentType;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\FinancialAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Shared\ProcessAdjustmentService;

class FinancialAdjustmentService
{
    public function __construct(private ProcessAdjustmentService $processAdjustmentService) {}

    public function list($request)
    {
        $user = auth()->user();
        $query = FinancialAdjustment::query()
            ->with('requestedFor', 'requestedBy')
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
            ->latest();

        return $query->paginate(
            PaginationEnum::GeneralPagination->value
        );
    }

    public function create(array $data)
    {
        $authUser = Auth::user();

        return DB::transaction(function () use ($data, $authUser) {
            $status = ($data['type'] == FinancialAdjustmentType::BONUS_REQUEST->value) ? 'approved' : 'pending';
            $financialAdjustment = FinancialAdjustment::create([
                'amount' => $data['amount'],
                'type' => $data['type'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                'status' => $status,

                'requested_by_type' => AppUser::class,
                'requested_by_id'   => $authUser->id,

                'requested_for_type' => AppUser::class,
                'requested_for_id' => $data['requested_for_id']
            ]);
            if ($data['type'] == FinancialAdjustmentType::BONUS_REQUEST->value)
                $this->processAdjustmentService->approveBonus($financialAdjustment);

            $financialAdjustment->load('requestedBy');

            return $financialAdjustment;
        });
    }

    public function show(FinancialAdjustment $financialAdjustment)
    {
        $user = auth()->user();

        $requestedFor = $financialAdjustment->requestedFor;

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
        } else {

            $canView = (
                (
                    $financialAdjustment->requested_for_type
                    == AppUser::class
                    &&
                    $financialAdjustment->requested_for_id
                    == $user->id
                )
            );

            if (!$canView) {
                throw new CustomException('غير مسموح بعرض الطلب');
            }
        }

        $financialAdjustment->load(
            'requestedFor',
            'requestedBy',
            'reviewedBy'
        );

        return $financialAdjustment;
    }
}
