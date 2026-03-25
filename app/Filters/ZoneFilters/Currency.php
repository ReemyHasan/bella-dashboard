<?php

namespace App\Filters\ZoneFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Currency extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('currency_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
