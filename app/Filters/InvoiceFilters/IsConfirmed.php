<?php

namespace App\Filters\InvoiceFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class IsConfirmed extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('is_confirmed', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
