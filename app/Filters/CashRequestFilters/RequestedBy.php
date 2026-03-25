<?php

namespace App\Filters\CashRequestFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class RequestedBy extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereHas('requestedBy', function ($q) use ($value) {
            $q->where(function ($sub) use ($value) {
                $sub->where('first_name', 'like', "%{$value}%")
                    ->orWhere('last_name', 'like', "%{$value}%")
                    ->orWhere('mobile', 'like', "%{$value}%")
                    ->orWhere('user_name', 'like', "%{$value}%");
            });
        });
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
