<?php

namespace App\Filters\FinancialAdjustmentFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class CreatedOrAssigned extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $user = auth()->user();

        /*
        |--------------------------------------------------------------------------
        | created_by_me
        |--------------------------------------------------------------------------
        */
        if ($value === 'created_by_me') {

            $this->query->where(function ($q) use ($user) {

                $q->where('requested_by_type', get_class($user))
                    ->where('requested_by_id', $user->id);
            });

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | assigned_to_me
        |--------------------------------------------------------------------------
        */
        if ($value === 'created_for_me') {

            $this->query->where(function ($q) use ($user) {
                $q->where('requested_for_type', get_class($user))
                    ->where('requested_for_id', $user->id);
            });
        }
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
