<?php
namespace App\Filters;

interface FilterContract
{
    public function handle($value): void;
    public function handleRange($value): void;

}
