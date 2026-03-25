<?php

namespace App\Filters\SubTeamFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class IsDirect extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('is_direct', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
