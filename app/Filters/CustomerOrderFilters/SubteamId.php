<?php

namespace App\Filters\CustomerOrderFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class SubteamId extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('sub_team_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
