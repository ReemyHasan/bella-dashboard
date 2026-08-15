<?php

namespace App\Filters\CustomerOrderFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Marketer extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('app_user_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
