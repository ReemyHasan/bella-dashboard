<?php

namespace App\Services\Shared;

use App\Enums\FinancialAdjustmentType;
use App\Enums\VaultTransactionType;
use App\Exceptions\CustomException;
use App\Models\DashUser;
use App\Models\FinancialAdjustment;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ProcessAdjustmentService
{
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

    public function buildNote($isBonus, $amount, $isDashUser)
    {

        $actionText = $isBonus ? 'مكافأة' : 'خصم';
        $DashUserText = $isDashUser ? 'من قبل الإدارة' : 'من قبل المدير';

        return "{$actionText} بقيمة " . number_format($amount, 2) . $DashUserText;
    }

    public function processAdjustment(FinancialAdjustment $adjustment)
    {
        $target = $adjustment->requestedFor;
        $requester = $adjustment->requestedBy;

        $amount = $adjustment->amount;

        $targetBefore = $target->balance;
        $requesterBefore = $requester->balance ?? null;

        $isDashUser = $adjustment->requested_by_type == DashUser::class;
        $isBonus =  ($adjustment->type == FinancialAdjustmentType::BONUS_ORDER->value ||  $adjustment->type == FinancialAdjustmentType::BONUS_REQUEST->value);

        $note = $this->buildNote($isBonus, $amount, $isDashUser);

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
                'notes' => $note,
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
            'notes' => "عملية ناتجة عن خصم أو مكافأة",

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
            'notes' => $note,

            'to_vault_balance_before' => $targetBefore,
            'to_vault_balance_after' => $targetAfter,
        ]);
    }
}
