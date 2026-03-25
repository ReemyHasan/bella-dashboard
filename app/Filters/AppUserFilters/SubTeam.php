<?php

namespace App\Filters\AppUserFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class SubTeam extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('subteam_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
