<?php

namespace App\Filters\AppUserRequestFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Type extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('user_request_type_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
