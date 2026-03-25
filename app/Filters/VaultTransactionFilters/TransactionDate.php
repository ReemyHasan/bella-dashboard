<?php

namespace App\Filters\VaultTransactionFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class TransactionDate extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereDate('transaction_date', $value);
    }

    public function handleRange($value): void
    {
        if (!empty($value['from']) && !empty($value['to'])) {
            $this->query->whereBetween('transaction_date', [$value['from'], $value['to']]);
        } elseif (!empty($value['from'])) {
            $this->query->whereDate('transaction_date', '>=', $value['from']);
        } elseif (!empty($value['to'])) {
            $this->query->whereDate('transaction_date', '<=', $value['to']);
        }
    }
}
