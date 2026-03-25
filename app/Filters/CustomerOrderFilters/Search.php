<?php

namespace App\Filters\CustomerOrderFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Search extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where(function ($query) use ($value) {

            $query->where('order_number', 'like', "%{$value}%");
            $query->orWhereHas('customer', function ($q) use ($value) {
                $q->where(function ($sub) use ($value) {
                    $sub->where('first_name', 'like', "%{$value}%")
                        ->orWhere('last_name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%")
                        ->orWhere('user_name', 'like', "%{$value}%");
                });
            });

            $query->orWhereHas('marketer', function ($q) use ($value) {
                $q->where(function ($sub) use ($value) {
                    $sub->where('first_name', 'like', "%{$value}%")
                        ->orWhere('last_name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%")
                        ->orWhere('user_name', 'like', "%{$value}%");
                });
            });



            $query->orWhereHas('warehouseMan', function ($q) use ($value) {
                $q->where(function ($sub) use ($value) {
                    $sub->where('first_name', 'like', "%{$value}%")
                        ->orWhere('last_name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%")
                        ->orWhere('user_name', 'like', "%{$value}%");
                });
            });

            $query->orWhereHas('teamleader', function ($q) use ($value) {
                $q->where(function ($sub) use ($value) {
                    $sub->where('first_name', 'like', "%{$value}%")
                        ->orWhere('last_name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%")
                        ->orWhere('user_name', 'like', "%{$value}%");
                });
            });

            $query->orWhereHas('manager', function ($q) use ($value) {
                $q->where(function ($sub) use ($value) {
                    $sub->where('first_name', 'like', "%{$value}%")
                        ->orWhere('last_name', 'like', "%{$value}%")
                        ->orWhere('mobile', 'like', "%{$value}%")
                        ->orWhere('user_name', 'like', "%{$value}%");
                });
            });
        });
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
