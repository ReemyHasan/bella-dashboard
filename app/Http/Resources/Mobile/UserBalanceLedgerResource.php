<?php

namespace App\Http\Resources\Mobile;

use App\Enums\VaultTransactionType;
use App\Models\BalanceTransferRequest;
use App\Models\CashRequest;
use App\Models\Competition;
use App\Models\CustomerOrder;
use App\Models\FinancialAdjustment;
use App\Models\VaultTransfer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserBalanceLedgerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'date' => $this->transaction_date,

            'type' => VaultTransactionType::from($this->type)->label(),

            'reference_type' => match ($this->reference_type) {
                BalanceTransferRequest::class => 'تحويل رصيد',
                CashRequest::class            => 'طلب نقدي',
                FinancialAdjustment::class    => 'تعديل مالي',
                VaultTransfer::class          => 'تحويل خزنة',
                CustomerOrder::class          => 'طلب عميل',
                Competition::class          => 'هدف تسويقي',
                default                       => 'غير معروف',
            },

            'reference_id' => $this->reference_id,
            'amount' => $this->amount,
            'balance_before' => $this->to_vault_balance_before,
            'balance_after' => $this->to_vault_balance_after,
            'notes' => $this->notes ?? 'N/A',
            'reason' => $this->reason ?? 'N/A',
        ];
    }
}
