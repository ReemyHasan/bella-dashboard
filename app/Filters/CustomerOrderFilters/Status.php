<?php

namespace App\Filters\CustomerOrderFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Status extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('order_status', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
