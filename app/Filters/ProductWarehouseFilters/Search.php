<?php

namespace App\Filters\ProductWarehouseFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Search extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {

        $this->query->whereHas('product', function ($query) use ($value) {
            $query->where(function ($q) use ($value) {
                $q->where('name', 'like', "%$value%")
                    ->orWhere('slug', 'like', "%$value%")
                    ->orWhere('country_of_origin', 'like', "%$value%");
            });
        });
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
