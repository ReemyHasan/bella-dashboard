<?php

namespace App\Filters\CashRequestFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;
use App\Models\AppUser;

class Marketer extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('requested_for_type', AppUser::class)->where(
            'requested_for_id',
            $value
        );
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
