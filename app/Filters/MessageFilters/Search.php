<?php

namespace App\Filters\MessageFilters;

use App\Filters\QueryFilter;
use App\Filters\FilterContract;

class Search extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $value = trim($value);

        if ($value !== '') {
            $this->query->whereRaw('LOWER(description) LIKE ?', ['%' . strtolower($value) . '%']);
        }
        // $this->query->where('description', 'ilike', "%$value%");
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
