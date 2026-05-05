<?php

namespace App\Filters\OfferWarehouseFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Active extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereHas('offer', function ($query) use ($value) {
            $query->where('active', $value);
        });
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
