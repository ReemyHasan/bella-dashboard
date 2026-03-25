<?php

namespace App\Filters\VaultTransactionFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Type extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('type', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
