<?php

namespace App\Filters\RoleFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class GuardName extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {

        $this->query->where('guard_name', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
