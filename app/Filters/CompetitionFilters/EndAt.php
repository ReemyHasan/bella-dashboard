<?php

namespace App\Filters\CompetitionFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class EndAt extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereDate('end_at', $value);
    }

    public function handleRange($value): void
    {
        if (!empty($value['from']) && !empty($value['to'])) {
            $this->query->whereBetween('end_at', [$value['from'], $value['to']]);
        } elseif (!empty($value['from'])) {
            $this->query->whereDate('end_at', '>=', $value['from']);
        } elseif (!empty($value['to'])) {
            $this->query->whereDate('end_at', '<=', $value['to']);
        }
    }
}
