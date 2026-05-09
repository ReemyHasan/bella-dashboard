<?php

namespace App\Filters\MessageFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class AppearDate extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $this->query->whereDate('appears_from', $value);
    }

    public function handleRange($value): void
    {
        $from = $value['from'] ?? null;
        $to   = $value['to'] ?? null;

        if ($from && $to) {
            $this->query->where(function ($query) use ($from, $to) {
                $query
                // ->whereBetween('appears_from', [$from, $to])
                //     ->orWhereBetween('appears_to', [$from, $to])
                    ->where(function ($q) use ($from, $to) {
                        $q->where('appears_from', '>=', $from)
                            ->where('appears_to', '<=', $to);
                    });
            });
        } elseif ($from) {
            $this->query->where('appears_to', '>=', $from);
        } elseif ($to) {
            $this->query->where('appears_from', '<=', $to);
        }
    }
}
