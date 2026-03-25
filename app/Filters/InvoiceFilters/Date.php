<?php

namespace App\Filters\InvoiceFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Date extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereDate('date', $value);
    }

    public function handleRange($value): void
    {
        if (!empty($value['from']) && !empty($value['to'])) {
            $this->query->whereBetween('date', [$value['from'], $value['to']]);
        } elseif (!empty($value['from'])) {
            $this->query->whereDate('date', '>=', $value['from']);
        } elseif (!empty($value['to'])) {
            $this->query->whereDate('date', '<=', $value['to']);
        }
    }
}
