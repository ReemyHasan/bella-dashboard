<?php

namespace App\Filters\SubTeamFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Team extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('team_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
