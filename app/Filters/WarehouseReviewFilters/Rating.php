<?php

namespace App\Filters\WarehouseReviewFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Rating extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('rating', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
