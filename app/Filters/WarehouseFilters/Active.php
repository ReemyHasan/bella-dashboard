<?php

namespace App\Filters\WarehouseFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Active extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('active', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
