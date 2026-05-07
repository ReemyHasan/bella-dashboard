<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;
use App\Http\Resources\Mobile\UserBalanceLedgerResource;
use App\Models\AppUser;
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

        return [
            'current_balance' => $user->balance,
            'from' => isset($from) ? $from->format('Y-m-d') : null,
            'to' => isset($to) ? $to->format('Y-m-d') : null,
            'transactions' => $this->returnPaginatedResponse($transactions, UserBalanceLedgerResource::collection($transactions))
        ];
    }
}
