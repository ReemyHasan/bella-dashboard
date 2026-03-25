<?php

namespace App\Filters\DashUserFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class mobile extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('mobile', 'like', "%$value%");
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
