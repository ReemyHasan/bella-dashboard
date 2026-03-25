<?php
namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;

class TeamReportExport implements FromCollection
{
    public function __construct(public $data) {}

    public function collection()
    {
        return collect($this->data);
    }
}