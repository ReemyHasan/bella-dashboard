<?php

namespace App\Filters\AppUserRequestFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class IsRead extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $value
            ? $this->query->whereNotNull('read_at')
            : $this->query->whereNull('read_at');
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
