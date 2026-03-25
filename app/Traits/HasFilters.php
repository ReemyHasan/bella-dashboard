<?php

namespace App\Traits;

use App\Filters\FilterBuilder;
use App\Filters\SortBuilder;

trait HasFilters
{
    public function scopeFilterBy($query, $filters, $namespace = null)
    {
        $namespace = $namespace ?? $this->defaultFilterNamespace();

        $filterBuilder = new FilterBuilder($query, $filters, $namespace);
        return $filterBuilder->apply();
    }

    public function scopeSortBy($query, $filters)
    {
        $sortBuilder = new SortBuilder($query, $filters);
        return $sortBuilder->apply();
    }

    protected function defaultFilterNamespace(): string
    {
        // Default: use model-specific namespace
        $modelName = class_basename($this);
        return "App\\Filters\\{$modelName}Filters";
    }
}
