<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SoldAndStagnantProductsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $rows = [];

    public function __construct($data)
    {
        // 🔥 Flatten data
        // dd($data);
        foreach ($data as $item) {

            $this->rows[] = [
                'product_name'   => $item['product_name'],
                'total_sold' => $item['total_sold'],
                'status' => $item['status'] == 'sold' ? 'مباعة' : 'راكدة'
            ];
        }
    }

    public function collection()
    {
        return collect($this->rows);
    }

    // ✅ Arabic Headers
    public function headings(): array
    {
        return [
            'اسم المنتج',
            'الكمية الكلية المباعة',
            'الحالة'
        ];
    }

    // ✅ Map each row
    public function map($row): array
    {
        return [
            $row['product_name'],
            $row['total_sold'],
            $row['status']
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('C:E')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);
        foreach (range('A', 'C') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->setRightToLeft(true);
        // 🔥 Header row styling
        $sheet->getStyle('A1:C1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'], // white text
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'], // blue background
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ]);

        return [];
    }
}
