<?php

namespace App\Filters\CashRequestFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class From extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where(function ($query) use ($value) {

            // Search vaults that have owners
            $query->whereHas('fromVault.owner', function ($q) use ($value) {
                $q->where(function ($sub) use ($value) {
                    $sub->where('first_name', 'like', "%{$value}%")
                        ->orWhere('last_name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%")
                        ->orWhere('user_name', 'like', "%{$value}%");
                });
            });

            // Handle company vault (owner_id = null)
            if (str_contains($value, 'شركة')) {
                $query->orWhereHas('fromVault', function ($q) {
                    $q->whereNull('owner_id');
                });
            }
        });
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
