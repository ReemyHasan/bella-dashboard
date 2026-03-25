<?php

namespace App\Filters\SubTeamFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class CreationDate extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereDate('created_at', $value);
    }

    public function handleRange($value): void
    {
        if (!empty($value['from']) && !empty($value['to'])) {
            $this->query->whereBetween('created_at', [$value['from'], $value['to']]);
        } elseif (!empty($value['from'])) {
            $this->query->whereDate('created_at', '>=', $value['from']);
        } elseif (!empty($value['to'])) {
            $this->query->whereDate('created_at', '<=', $value['to']);
        }
    }
}
