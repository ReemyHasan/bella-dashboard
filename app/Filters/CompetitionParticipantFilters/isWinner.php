<?php

namespace App\Filters\CompetitionParticipantFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class isWinner extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('is_winner', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
