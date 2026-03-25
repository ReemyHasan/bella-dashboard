<?php

namespace App\Filters\DashUserFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Role extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereHas('roles', function ($q) use ($value) {
            $q->where('id', $value);
        });
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
