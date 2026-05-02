<?php

namespace App\Services\Shared;

use App\Enums\VaultTransactionType;
use App\Models\AppUser;
use App\Models\CustomerOrder;
use App\Models\VaultTransaction;
use Illuminate\Support\Facades\DB;

class OrderSharedService
{

    public function generateOrderNumber(): string
    {
        $date = now()->format('Ymd');

        $lastOrder = CustomerOrder::whereDate('created_at', now()->toDateString())
            ->lockForUpdate()
            ->latest('id')
            ->first();

        $sequence = 1;

        if ($lastOrder && $lastOrder->order_number) {
            $lastSequence = (int) substr($lastOrder->order_number, -6);
            $sequence = $lastSequence + 1;
        }

        return 'ORD-' . $date . '-' . str_pad($sequence, 6, '0', STR_PAD_LEFT);
    }

    public function resolvePercentages($user, $team, $teamleaderId, $isDirectTeam)
    {
        $marketerId = $user->id;
        $managerId = $team->manager_id;

        $teamLeaderFinalId = $isDirectTeam ? $managerId : $teamleaderId;

        $marketerPercentage = $team->marketer_percentage;
        $teamLeaderPercentage = $team->team_leader_percentage;
        $managerPercentage = $team->manager_percentage;

        // =========================
        // 🔥 CASE HANDLING
        // =========================

        // 4️⃣ marketer = manager
        if ($marketerId == $managerId) {
            return [
                'teamleader_id' => null,
                'manager_id' => $managerId,

                'marketer_percentage' => $marketerPercentage + $teamLeaderPercentage,
                'teamleader_percentage' => 0,
                'manager_percentage' => 0,
            ];
        }

        // 3️⃣ marketer = team leader
        if ($marketerId == $teamLeaderFinalId) {
            return [
                'teamleader_id' => $teamLeaderFinalId,
                'manager_id' => $managerId,

                'marketer_percentage' => $marketerPercentage + $teamLeaderPercentage,
                'teamleader_percentage' => 0,
                'manager_percentage' => $managerPercentage,
            ];
        }

        // 2️⃣ direct subteam (leader = manager)
        if ($isDirectTeam && $teamLeaderFinalId == $managerId) {
            return [
                'teamleader_id' => $managerId,
                'manager_id' => $managerId,

                'marketer_percentage' => $marketerPercentage,
                'teamleader_percentage' => $teamLeaderPercentage,
                'manager_percentage' => 0, // avoid double paying
            ];
        }

        // 1️⃣ normal case
        return [
            'teamleader_id' => $teamLeaderFinalId,
            'manager_id' => $managerId,

            'marketer_percentage' => $marketerPercentage,
            'teamleader_percentage' => $teamLeaderPercentage,
            'manager_percentage' => $managerPercentage,
        ];
    }

    public function calculateAmounts($baseAmount, $resolved, $data, $current_exchange_rate)
    {
        $marketerAmount = $baseAmount * $resolved['marketer_percentage'] / 100;
        $teamLeaderAmount = $baseAmount * $resolved['teamleader_percentage'] / 100;
        $managerAmount = $baseAmount * $resolved['manager_percentage'] / 100;

        // 🔥 Apply adjustment ONLY to marketer
        if (!empty($data['adjustment_value'])) {

            $type = $data['adjustment_type'];        // percentage | fixed
            $operation = $data['adjustment_operation']; // increase | decrease
            $value = (float) $data['adjustment_value'];

            $adjustment = $type == 'percentage'
                ? ($baseAmount * $value / 100)
                : $value * $current_exchange_rate;

            if ($operation == 'increase') {
                $marketerAmount += $adjustment;
            } else {
                $marketerAmount -= $adjustment;
            }

            $marketerAmount = max(0, $marketerAmount);
        }

        return [
            'marketer_amount' => round($marketerAmount, 2),
            'teamleader_amount' => round($teamLeaderAmount, 2),
            'manager_amount' => round($managerAmount, 2),
        ];
    }
    public function handleFinancialProcess(CustomerOrder $order, $vault)
    {

        return DB::transaction(function () use ($order, $vault) {
            $marketerAmount   = $order->marketer_amount;
            $teamleaderAmount = $order->teamleader_amount;
            $managerAmount    = $order->manager_amount;



            $this->addBalance($vault, $order->app_user_id, $marketerAmount, VaultTransactionType::marketer_percentage->value, $order, $order->marketer_percentage);
            if ($order->teamleader_id) {
                $this->addBalance($vault, $order->teamleader_id, $teamleaderAmount, VaultTransactionType::teamleader_percentage->value, $order, $order->teamleader_percentage);
            }

            if ($order->manager_id) {
                $this->addBalance($vault, $order->manager_id, $managerAmount, VaultTransactionType::manager_percentage->value, $order, $order->manager_percentage);
            }

            $order->update([
                'is_financial_processed' => true
            ]);
            return $order->refresh();
        });
    }

    public function addBalance($vault, $userId, $amount, $type, $order, $percentage = null)
    {
        if (!$userId || $amount <= 0) return;

        $user = AppUser::lockForUpdate()->find($userId);

        $before = $user->balance;
        $after = $before + $amount;

        $user->update([
            'balance' => $after
        ]);

        $vault->update([
            'balance' => $vault->balance - $amount,
        ]);
        $this->createCompleteTransaction($vault, $userId, $amount, $before, $after, $type, $order, $percentage);
    }

    public function subtractBalance($vault, $userId, $amount, $type, $order, $percentage)
    {
        if (!$userId || $amount <= 0) return;

        $user = AppUser::lockForUpdate()->find($userId);

        // if ($user->balance < $amount) {
        //     throw new CustomException('الرصيد غير كافٍ للاسترجاع');
        // }

        $before = $user->balance;
        $after = $before - $amount;

        $user->update([
            'balance' => $after
        ]);

        $vault->update([
            'balance' => $vault->balance + $amount,
        ]);
        $this->createRefundTransaction($vault, $userId, $amount, $before, $after, $type, $order, $percentage);
    }
    public function buildNote($percentage, $amount, $action = 'add')
    {
        if ($percentage === null) return null;

        $actionText = $action === 'add' ? 'إضافة' : 'خصم';

        return "{$actionText} نسبة {$percentage}% بقيمة " . number_format($amount, 2);
    }
    public function createRefundTransaction($vault, $appUserId, $amount, $before, $after, $type, $order, $percentage)
    {
        $user = auth()->user();
        $note = $this->buildNote($percentage, $amount, 'subtract');
        VaultTransaction::create([
            'to_vault_id' => $vault->id,

            'type' => VaultTransactionType::ORDER_REFUND->value,

            'amount' => $amount,

            'transaction_date' => now(),

            'notes' => null,

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'reference_type' => CustomerOrder::class,
            'reference_id' => $order->id,

            'to_vault_balance_before' => $vault->balance,
            'to_vault_balance_after' => $vault->balance + $amount,
        ]);
        VaultTransaction::create([
            'type' => $type,
            'amount' => abs($amount),
            'transaction_date' => now(),

            'reference_type' => CustomerOrder::class,
            'reference_id' => $order->id,


            'balance_user_type' => AppUser::class,
            'balance_user_id' => $appUserId,

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'to_vault_balance_before' => $before,
            'to_vault_balance_after' => $after,
            'notes' => $note,

        ]);
    }


    public function createCompleteTransaction($vault, $appUserId, $amount, $before, $after, $type, $order, $percentage)
    {
        $user = auth()->user();
        $note = $this->buildNote($percentage, $amount, 'add');
        VaultTransaction::create([
            'to_vault_id' => $vault->id,

            'type' => VaultTransactionType::ORDER_COMPLETE->value,

            'amount' => $amount,

            'transaction_date' => now(),

            'notes' => null,

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'reference_type' => CustomerOrder::class,
            'reference_id' => $order->id,

            'to_vault_balance_before' => $vault->balance,
            'to_vault_balance_after' => $vault->balance - $amount,
        ]);
        VaultTransaction::create([
            'type' => $type,
            'amount' => abs($amount),
            'transaction_date' => now(),

            'reference_type' => CustomerOrder::class,
            'reference_id' => $order->id,


            'balance_user_type' => AppUser::class,
            'balance_user_id' => $appUserId,

            'action_by_type' => get_class($user),
            'action_by_id' => $user->id,

            'to_vault_balance_before' => $before,
            'to_vault_balance_after' => $after,
            'notes' => $note
        ]);
    }
}
