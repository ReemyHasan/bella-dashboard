<?php

namespace App\Filters\CityFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Zone extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('zone_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
