<?php

namespace App\Filters\RegionFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class City extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('city_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
