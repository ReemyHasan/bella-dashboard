<?php

namespace App\Filters\RoleFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Search extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where(function($q) use ($value) {
              $q->where('name_ar', 'like', "%$value%")
              ->orWhere('name', 'like', "%$value%");
        });
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
