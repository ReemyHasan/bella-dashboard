<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class OrdersWarehouseManExport implements FromCollection, WithHeadings, WithStyles
{
    protected $rows = [];

    public function __construct($data)
    {
        foreach ($data['warehouse_men'] as $man) {

            $this->rows[] = [
                'date' => $data['date'],
                'name' => $man['name'],
                'orders' => $man['orders_count'],
                'total_price' => round($man['total_price'], 2),
                'deduction' => round($man['total_deduction'], 2),
                'tips' => round($man['total_tips'], 2),
            ];
        }
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return [
            'التاريخ',
            'الموظف',
            'عدد الطلبات',
            'إجمالي السعر',
            'الخصومات',
            'الإكراميات',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        // Header styling
        $sheet->getStyle('A1:G1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
