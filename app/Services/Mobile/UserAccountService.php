<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;
use App\Http\Resources\Mobile\UserBalanceLedgerResource;
use App\Models\AppUser;
use App\Models\CustomerOrder;
use App\Models\VaultTransaction;
use Carbon\Carbon;
use App\Traits\ResultTrait;

class UserAccountService
{
    use ResultTrait;

    public function userBalanceLedger(array $filters)
    {
        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null;
        $to   = isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : null;

        $user = auth()->user();

        $transactions = VaultTransaction::where('balance_user_type', AppUser::class)
            ->where('balance_user_id', $user->id)

            ->when($from && $to, function ($q) use ($from, $to) {
                $q->whereBetween('transaction_date', [$from, $to]);
            })
            ->when($from && !$to, function ($q) use ($from) {
                $q->where('transaction_date', '>=', $from);
            })
            ->when(!$from && $to, function ($q) use ($to) {
                $q->where('transaction_date', '<=', $to);
            })
            ->orderByDesc('transaction_date')
            ->paginate(PaginationEnum::GeneralPagination->value);
        $orderIds = $transactions->getCollection()
            ->where('reference_type', CustomerOrder::class)
            ->pluck('reference_id')
            ->filter()
            ->unique()
            ->values();

        $orderNumbers = CustomerOrder::whereIn('id', $orderIds)
            ->pluck('order_number', 'id');

        $transactions->getCollection()->transform(function ($transaction) use ($orderNumbers) {

            if ($transaction->reference_type === CustomerOrder::class) {
                $transaction->reference_order_number =
                    $orderNumbers->get($transaction->reference_id);
            }

            return $transaction;
        });
        return [
            'current_balance' => $user->balance,
            'from' => isset($from) ? $from->format('Y-m-d') : null,
            'to' => isset($to) ? $to->format('Y-m-d') : null,
            'transactions' => $this->returnPaginatedResponse($transactions, UserBalanceLedgerResource::collection($transactions))
        ];
    }
}
