<?php

namespace App\Filters\AddressFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Region extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('region_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
