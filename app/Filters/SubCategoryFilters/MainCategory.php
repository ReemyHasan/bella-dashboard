<?php

namespace App\Filters\SubCategoryFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class MainCategory extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('main_category_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
