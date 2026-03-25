<?php
namespace App\Filters;

class SortBuilder
{
    protected $query;
    protected $sorts;

    public function __construct($query, $sorts)
    {
        $this->query = $query;
        $this->sorts = $sorts;
    }

    public function apply()
    {
        foreach ($this->sorts as $field => $direction) {
            $this->query->orderBy($field, $direction);
        }

        return $this->query;
    }
}
