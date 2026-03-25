<?php

namespace App\Filters\WarehouseHandoverFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class RequesterWarehouse extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('requester_warehouse_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
