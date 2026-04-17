<?php

namespace App\Services\DashUser\Reports;

use App\Enums\VaultTransactionType;
use App\Models\BalanceTransferRequest;
use App\Models\CashRequest;
use App\Models\CustomerOrder;
use App\Models\FinancialAdjustment;
use App\Models\Vault;
use App\Models\VaultTransaction;
use App\Models\VaultTransfer;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class VaultReportService
{
    public function vaultsSummaryReport(array $filters)
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();

        // ✅ IN per vault
        $inTransactions = VaultTransaction::whereBetween('transaction_date', [$from, $to])
            ->whereNotNull('to_vault_id')
            ->select(
                'to_vault_id',
                DB::raw('SUM(amount) as total_in')
            )
            ->groupBy('to_vault_id')
            ->get()
            ->keyBy('to_vault_id');

        // ✅ OUT per vault
        $outTransactions = VaultTransaction::whereBetween('transaction_date', [$from, $to])
            ->whereNotNull('from_vault_id')
            ->select(
                'from_vault_id',
                DB::raw('SUM(amount) as total_out')
            )
            ->groupBy('from_vault_id')
            ->get()
            ->keyBy('from_vault_id');

        $vaults = Vault::with('owner:id,first_name,last_name')->get();

        $data = $vaults->map(function ($vault) use ($inTransactions, $outTransactions) {

            $in = $inTransactions[$vault->id]->total_in ?? 0;
            $out = $outTransactions[$vault->id]->total_out ?? 0;

            return [
                'vault_id' => $vault->id,
                'owner' => $vault->id == 1 ? "خزنة الشركة" : $vault->owner?->first_name . ' ' . $vault->owner?->last_name,
                'balance' => (float) $vault->balance,
                'total_in' => (float) $in,
                'total_out' => (float) $out,
            ];
        });

        return [
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'vaults' => $data,
            'total_balances' => $data->sum('balance'),
        ];
    }

    public function vaultDetailsReport(array $filters)
    {
        $from = Carbon::parse($filters['from'])->startOfDay();
        $to = Carbon::parse($filters['to'])->endOfDay();

        $vault = Vault::with('owner')->findOrFail($filters['vault_id']);

        $transactions = VaultTransaction::where(function ($q) use ($vault) {
            $q->where('from_vault_id', $vault->id)
                ->orWhere('to_vault_id', $vault->id);
        })
            ->whereBetween('transaction_date', [$from, $to])
            ->orderBy('transaction_date')
            ->get()
            ->map(function ($trx) use ($vault) {

                return [
                    'id' => $trx->id,
                    'date' => $trx->transaction_date,
                    'type' => VaultTransactionType::from($trx->type)->label(),
                    // 'type' => $trx->type,
                    'amount' => $trx->amount,

                    'direction' => $trx->directionForVault($vault->id),

                    'from_balance_before' => $trx->from_vault_balance_before,
                    'from_balance_after' => $trx->from_vault_balance_after,

                    'to_balance_before' => $trx->to_vault_balance_before,
                    'to_balance_after' => $trx->to_vault_balance_after,

                    'reason' => $trx->reason,
                    'notes' => $trx->notes,

                    'reference_type' => match ($trx->reference_type) {
                        BalanceTransferRequest::class => 'تحويل رصيد',
                        CashRequest::class            => 'طلب نقدي',
                        FinancialAdjustment::class    => 'تعديل مالي',
                        VaultTransfer::class          => 'تحويل خزنة',
                        CustomerOrder::class          => 'طلب عميل',
                        default                       => 'غير معروف',
                    },
                    'reference_id' => $trx->reference_id,
                ];
            });

        return [
            'vault' => [
                'id' => $vault->id,
                'owner' => $vault->owner?->first_name . ' ' . $vault->owner?->last_name,
                'balance' => $vault->balance,
            ],
            'from' => $from->format('Y-m-d'),
            'to' => $to->format('Y-m-d'),
            'transactions' => $transactions,
        ];
    }
}
