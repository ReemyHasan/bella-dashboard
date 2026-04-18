<?php

namespace App\Services\DashUser;

use App\Enums\FinancialAdjustmentType;
use App\Enums\PaginationEnum;
use App\Enums\VaultTransactionType;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\DashUser;
use App\Models\FinancialAdjustment;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FinancialAdjustmentService
{
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
                $this->approveBonus($financialAdjustment);

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
                $this->approveBonus($financialAdjustment);

            $financialAdjustment->load('fromVault.owner', 'toVault.owner', 'requestedBy');

            return $financialAdjustment;
        });
    }

    public function approveBonus(FinancialAdjustment $financialAdjustment)
    {
        return DB::transaction(function () use ($financialAdjustment) {

            $financialAdjustment->update([
                'reviewed_by' => Auth::id(),
                'reviewed_at' => now(),
            ]);
            $this->processAdjustment($financialAdjustment);
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
            $this->processAdjustment($financialAdjustment);
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

    private function processAdjustment(FinancialAdjustment $adjustment)
    {
        $target = $adjustment->requestedFor;
        $requester = $adjustment->requestedBy;

        $amount = $adjustment->amount;

        $targetBefore = $target->balance;
        $requesterBefore = $requester->balance ?? null;

        $isDashUser = $adjustment->requested_by_type == DashUser::class;
        $isBonus =  ($adjustment->type == FinancialAdjustmentType::BONUS_ORDER->value ||  $adjustment->type == FinancialAdjustmentType::BONUS_REQUEST->value);

        // =========================
        // ✅ CASE 1: DASH USER
        // =========================
        if ($isDashUser) {

            if ($isBonus) {
                $targetAfter = $targetBefore + $amount;
            } else {
                if ($targetBefore < $amount) {
                    throw new CustomException("الرصيد غير كافٍ {$targetBefore}.");
                }

                $targetAfter = $targetBefore - $amount;
            }

            $target->update([
                'balance' => $targetAfter
            ]);

            VaultTransaction::create([
                'balance_user_type' => get_class($target),
                'balance_user_id' => $target->id,

                'type' => $adjustment->type,

                'amount' => $amount,
                'transaction_date' => now(),

                'reference_type' => FinancialAdjustment::class,
                'reference_id' => $adjustment->id,

                'action_by_type' => get_class(Auth::user()),
                'action_by_id' => Auth::id(),

                'to_vault_balance_before' => $targetBefore,
                'to_vault_balance_after' => $targetAfter,
            ]);

            return;
        }

        // =========================
        // ✅ CASE 2: APP USER
        // =========================

        if (!$requester) {
            throw new CustomException('مقدم الطلب غير موجود.');
        }

        if ($isBonus) {
            // requester → target

            if ($requesterBefore < $amount) {
                throw new CustomException("رصيد مقدم الطلب غير كافٍ {$requesterBefore}.");
            }

            $requesterAfter = $requesterBefore - $amount;
            $targetAfter = $targetBefore + $amount;
        } else {
            // target → requester

            if ($targetBefore < $amount) {
                throw new CustomException("الرصيد غير كافٍ {$targetBefore}.");
            }

            $requesterAfter = $requesterBefore + $amount;
            $targetAfter = $targetBefore - $amount;
        }

        // ✅ Update balances
        $requester->update([
            'balance' => $requesterAfter
        ]);

        $target->update([
            'balance' => $targetAfter
        ]);

        // =========================
        // ✅ TRANSACTION 1 (Requester)
        // =========================
        VaultTransaction::create([
            'balance_user_type' => get_class($requester),
            'balance_user_id' => $requester->id,

            'type' => $isBonus
                ? VaultTransactionType::TRANSFER_OUT->value
                : VaultTransactionType::TRANSFER_IN->value,

            'amount' => $amount,
            'transaction_date' => now(),

            'reference_type' => FinancialAdjustment::class,
            'reference_id' => $adjustment->id,

            'action_by_type' => get_class(Auth::user()),
            'action_by_id' => Auth::id(),

            'to_vault_balance_before' => $requesterBefore,
            'to_vault_balance_after' => $requesterAfter,
        ]);

        // =========================
        // ✅ TRANSACTION 2 (Target)
        // =========================
        VaultTransaction::create([
            'balance_user_type' => get_class($target),
            'balance_user_id' => $target->id,

            'type' => $adjustment->type,

            'amount' => $amount,
            'transaction_date' => now(),

            'reference_type' => FinancialAdjustment::class,
            'reference_id' => $adjustment->id,

            'action_by_type' => get_class(Auth::user()),
            'action_by_id' => Auth::id(),

            'to_vault_balance_before' => $targetBefore,
            'to_vault_balance_after' => $targetAfter,
        ]);
    }
}
