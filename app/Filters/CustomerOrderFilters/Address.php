<?php

namespace App\Filters\CustomerOrderFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Address extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('address_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
