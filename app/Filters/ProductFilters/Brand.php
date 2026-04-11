<?php

namespace App\Filters\ProductFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Brand extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('brand_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
