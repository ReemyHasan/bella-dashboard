<?php

namespace App\Filters\CashRequestFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class DeliveredAt extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereDate('delivered_at', $value);
    }

    public function handleRange($value): void
    {
        if (!empty($value['from']) && !empty($value['to'])) {
            $this->query->whereBetween('delivered_at', [$value['from'], $value['to']]);
        } elseif (!empty($value['from'])) {
            $this->query->whereDate('delivered_at', '>=', $value['from']);
        } elseif (!empty($value['to'])) {
            $this->query->whereDate('delivered_at', '<=', $value['to']);
        }
    }
}
