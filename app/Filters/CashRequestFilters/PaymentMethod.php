<?php

namespace App\Filters\CashRequestFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class PaymentMethod extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->where('payment_method_id', $value);
    }

    public function handleRange($value): void
    {
        // Not applicable
    }
}
