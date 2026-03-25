<?php

namespace App\Filters\SubCategoryFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Search extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('name', 'like', "%$value%")->orWhereHas('mainCategory', function ($q) use ($value) {
                      $q->where('name', 'like', "%{$value}%");
                  });
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
