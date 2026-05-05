<?php

namespace App\Filters\OfferWarehouseFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Search extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {

        $this->query->whereHas('offer', function ($query) use ($value) {
            $query->where(function ($q) use ($value) {
                $q->where('name', 'like', "%$value%")
                    ->orWhere('symbol', 'like', "%$value%")
                    ->orWhere('description', 'like', "%$value%")
                    ->orWhere('marketing_description', 'like', "%$value%")
                    ->orWhere('summary', 'like', "%$value%");
            });
        });
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
