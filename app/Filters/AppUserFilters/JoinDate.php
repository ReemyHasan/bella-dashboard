<?php

namespace App\Filters\AppUserFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class JoinDate extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereDate('join_date', $value);
    }

    public function handleRange($value): void
    {
        if (!empty($value['from']) && !empty($value['to'])) {
            $this->query->whereBetween('join_date', [$value['from'], $value['to']]);
        } elseif (!empty($value['from'])) {
            $this->query->whereDate('join_date', '>=', $value['from']);
        } elseif (!empty($value['to'])) {
            $this->query->whereDate('join_date', '<=', $value['to']);
        }
    }
}
