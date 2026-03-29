<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MarketerDailyExport implements FromCollection, WithHeadings, WithStyles
{
    protected $rows = [];

    public function __construct($data)
    {
        foreach ($data['days'] as $day) {
            $this->rows[] = [
                'date' => $day['date'],
                'orders' => $day['total_orders'],
                'sales' => round($day['total_sales'], 2),
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
            'عدد الطلبات',
            'إجمالي المبيعات',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        // Header styling
        $sheet->getStyle('A1:F1')->applyFromArray([
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

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
