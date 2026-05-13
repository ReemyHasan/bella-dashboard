<?php

namespace App\Services\DashUser;

use App\Enums\FinancialAdjustmentType;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\DashUser;
use App\Models\FinancialAdjustment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\Shared\ProcessAdjustmentService;

class FinancialAdjustmentService
{
    public function __construct(private ProcessAdjustmentService $processAdjustmentService) {}

    public function list($request)
    {
        return FinancialAdjustment::with('fromVault.owner', 'toVault.owner', 'requestedFor', 'requestedBy')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        $authUser = Auth::user();

        return DB::transaction(function () use ($data, $authUser) {

            $isOrder   = str_contains($data['type'], 'order');
            $isRequest = str_contains($data['type'], 'request');

            // =========================
            // ✅ Resolve requested_by
            // =========================
            if ($isOrder) {
                // DashUser creates directly
                $requestedByType = get_class($authUser);
                $requestedById   = $authUser->id;
            } else {
                // Request → AppUser (leader/manager)
                $requestedByType = AppUser::class;
                $requestedById   = $data['requested_by_id'];
            }

            $status = ($data['type'] == FinancialAdjustmentType::BONUS_ORDER->value || $data['type'] == FinancialAdjustmentType::BONUS_REQUEST->value) ? 'approved' : 'pending';
            $financialAdjustment = FinancialAdjustment::create([
                // 'from_vault_id' => 1,
                'amount' => $data['amount'],
                'type' => $data['type'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                'status' => $status,

                'requested_by_type' => $requestedByType,
                'requested_by_id'   => $requestedById,

                'requested_for_type' => $data['requested_for_type'] == 'dash_user' ? DashUser::class : AppUser::class,
                'requested_for_id' => $data['requested_for_id']
            ]);
            if ($data['type'] == FinancialAdjustmentType::BONUS_ORDER->value || $data['type'] == FinancialAdjustmentType::BONUS_REQUEST->value)
                $this->processAdjustmentService->approveBonus($financialAdjustment);

            $financialAdjustment->load('fromVault.owner', 'toVault.owner', 'requestedBy');

            return $financialAdjustment;
        });
    }

    public function update(FinancialAdjustment $financialAdjustment, array $data)
    {
        if ($financialAdjustment->status !== 'pending') {
            throw new CustomException('لا يمكن تعديل الطلب بعد مراجعته.');
        }
        $authUser = Auth::user();

        return DB::transaction(function () use ($financialAdjustment, $data, $authUser) {

            $isOrder   = str_contains($data['type'], 'order');
            $isRequest = str_contains($data['type'], 'request');

            // =========================
            // ✅ Resolve requested_by
            // =========================
            if ($isOrder) {
                // DashUser creates directly
                $requestedByType = get_class($authUser);
                $requestedById   = $authUser->id;
            } else {
                // Request → AppUser (leader/manager)
                $requestedByType = AppUser::class;
                $requestedById   = $data['requested_by_id'];
            }
            $status = ($data['type'] == FinancialAdjustmentType::BONUS_ORDER->value || $data['type'] == FinancialAdjustmentType::BONUS_REQUEST->value) ? 'approved' : 'pending';

            $financialAdjustment->update([
                // 'from_vault_id' => 1,
                'amount' => $data['amount'],
                'type' => $data['type'],
                'reason' => $data['reason'] ?? null,
                'notes' => $data['notes'] ?? null,

                'status' => $status,
                'requested_by_type' => $requestedByType,
                'requested_by_id'   => $requestedById,
                'requested_for_type' => $data['requested_for_type'] == 'dash_user' ? DashUser::class : AppUser::class,
                'requested_for_id' => $data['requested_for_id']
            ]);
            if ($data['type'] == FinancialAdjustmentType::BONUS_ORDER->value || $data['type'] == FinancialAdjustmentType::BONUS_REQUEST->value)
                $this->processAdjustmentService->approveBonus($financialAdjustment);

            $financialAdjustment->load('fromVault.owner', 'toVault.owner', 'requestedBy');

            return $financialAdjustment;
        });
    }

    public function show(FinancialAdjustment $financialAdjustment)
    {
        $financialAdjustment->load('fromVault.owner', 'toVault.owner', 'requestedFor', 'requestedBy', 'reviewedBy');
        return $financialAdjustment;
    }

    public function delete(FinancialAdjustment $financialAdjustment)
    {
        if ($financialAdjustment->status == 'approved') {
            throw new CustomException('لا يمكن حذف الطلب بعد معالجته.');
        }
        return $financialAdjustment->delete();
    }


    public function handle(FinancialAdjustment $financialAdjustment, array $data)
    {
        $status = $data['status'];

        return match ($status) {
            'approved' =>
            $this->approve(
                $financialAdjustment,
                $data['notes'] ?? null
            ),

            'rejected' =>
            $this->reject(
                $financialAdjustment,
                $data['notes'] ?? null
            ),

            default => throw new CustomException('يوجد مشكلة بالمعلومات المدخلة.')
        };
    }


    public function approve(FinancialAdjustment $financialAdjustment, ?string $notes = null)
    {
        if ($financialAdjustment->status !== 'pending') {
            throw new CustomException('لا يمكن الموافقة على الطلب, لقد تم معالجته بالفعل.');
        }

        return DB::transaction(function () use ($financialAdjustment, $notes) {

            $financialAdjustment->update([
                'review_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'status' => 'approved',
            ]);
            $financialAdjustment->refresh();
            $this->processAdjustmentService->processAdjustment($financialAdjustment);
            return $financialAdjustment;
        });
    }
    public function reject(FinancialAdjustment $financialAdjustment, ?string $notes = null)
    {
        if ($financialAdjustment->status !== 'pending') {
            throw new CustomException('لا يمكن رفض على الطلب, لقد تم معالجته بالفعل.');
        }

        return DB::transaction(function () use ($financialAdjustment, $notes) {

            $financialAdjustment->update([
                'review_notes' => $notes,
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
                'status' => 'rejected',
            ]);

            return $financialAdjustment->refresh();
        });
    }
}
