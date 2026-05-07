<?php

namespace App\Services\DashUser\Reports;

use App\Enums\VaultTransactionType;
use App\Models\AppUser;
use App\Models\BalanceTransferRequest;
use App\Models\CashRequest;
use App\Models\Competition;
use App\Models\CustomerOrder;
use App\Models\FinancialAdjustment;
use App\Models\VaultTransaction;
use App\Models\VaultTransfer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UserAccountReportService
{
    public function userBalanceLedger(array $filters)
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to   = Carbon::parse($filters['to'])->endOfDay();

        $user = AppUser::findOrFail($filters['user_id']);

        $transactions = VaultTransaction::where('balance_user_type', AppUser::class)
            ->where('balance_user_id', $user->id)

            // ->where('to_vault_balance_before', '>=', 0)
            ->whereBetween('transaction_date', [$from, $to])
            // ->where('action_by_id', $user->id)
            ->orderBy('transaction_date')
            ->get()
            ->map(function ($trx) use ($user) {
                return [
                    'date' => $trx->transaction_date,
                    // 'type' => __('constant.' . $trx->type),
                    'type' => VaultTransactionType::from($trx->type)->label(),

                    'reference_type' => match ($trx->reference_type) {
                        BalanceTransferRequest::class => 'تحويل رصيد',
                        CashRequest::class            => 'طلب نقدي',
                        FinancialAdjustment::class    => 'تعديل مالي',
                        VaultTransfer::class          => 'تحويل خزنة',
                        CustomerOrder::class          => 'طلب عميل',
                        Competition::class          => 'هدف تسويقي',
                        default                       => 'غير معروف',
                    },
                    'reference_id' => $trx->reference_id,
                    'amount' => $trx->amount,
                    'balance_before' => $trx->to_vault_balance_before,
                    'balance_after' => $trx->to_vault_balance_after,
                    'notes' => $trx->notes ?? "N/A",
                    'reason' => $trx->reason ?? "N/A",
                ];
            });

        return [
            'user' => [
                'id' => $user->id,
                'name' => $user->first_name . ' ' . $user->last_name,
                'current_balance' => $user->balance,
            ],
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'transactions' => $transactions,
        ];
    }
}
