<?php

namespace App\Filters\WarehouseFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class IsMain extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('is_main', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
