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

class ProductZoneReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $rows = [];

    public function __construct($data)
    {
        // 🔥 Flatten data
        // dd($data);
        foreach ($data as $zone) {

            $this->rows[] = [
                'product_name'   => $zone['product'],
                'zone_name' => $zone['zone'],
                'price' => $zone['price'],
                'is_available' => $zone['is_available'] == 1 ? 'Yes' : 'No',
                'price_after_adjustment' => $zone['price_after_adjustment'] ?? $zone['price']
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
            'المنطقة الجغرافية',
            'السعر',
            'التوفر',
            'السعر بعد التعديل',
        ];
    }

    // ✅ Map each row
    public function map($row): array
    {
        return [
            $row['product_name'],
            $row['zone_name'],
            $row['price'],
            $row['is_available'],
            $row['price_after_adjustment'],
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('C:E')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);
        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->setRightToLeft(true);
        // 🔥 Header row styling
        $sheet->getStyle('A1:E1')->applyFromArray([
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
