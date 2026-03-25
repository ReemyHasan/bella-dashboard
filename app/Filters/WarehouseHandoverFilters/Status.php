<?php

namespace App\Filters\WarehouseHandoverFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Status extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('status', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
