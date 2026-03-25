<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class WarehouseReportExport implements FromCollection
{
    public function __construct(public $data) {}

    public function collection()
    {
        return collect($this->data);
    }
}