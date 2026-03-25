<?php

namespace App\Filters\InvoiceFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Warehouse extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('warehouse_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
