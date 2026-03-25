<?php

namespace App\Filters\ProductWarehouseFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class SubCategory extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        
        $this->query->whereHas('product', function ($query) use ($value) {
            $query->where('sub_category_id', $value);
        });
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
