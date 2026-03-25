<?php

namespace App\Filters\VaultTransferFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class TransferredAt extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereDate('transferred_at', $value);
    }

    public function handleRange($value): void
    {
        if (!empty($value['from']) && !empty($value['to'])) {
            $this->query->whereBetween('transferred_at', [$value['from'], $value['to']]);
        } elseif (!empty($value['from'])) {
            $this->query->whereDate('transferred_at', '>=', $value['from']);
        } elseif (!empty($value['to'])) {
            $this->query->whereDate('transferred_at', '<=', $value['to']);
        }
    }
}
