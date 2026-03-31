<?php

namespace App\Filters\AppUserFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class IsWarehouseMan extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('is_warehouse_man', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
