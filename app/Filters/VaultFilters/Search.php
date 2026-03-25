<?php

namespace App\Filters\VaultFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Search extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereHas('owner', function ($query) use ($value) {

            $query->where(function ($q) use ($value) {
                $q->where('first_name', 'like', "%$value%")
                    ->orWhere('mobile', 'like', "%$value%")
                    ->orWhere('last_name', 'like', "%$value%")
                    ->orWhere('user_name', 'like', "%$value%");
            });

            if (str_contains($value, 'شركة')) {
                $query->orWhereNull('owner_id');
            }
        });
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
