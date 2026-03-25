<?php

namespace App\Filters\AppUserRequestFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class IsHandled extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $value
            ? $this->query->whereNotNull('handled_at')
            : $this->query->whereNull('handled_at');
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
