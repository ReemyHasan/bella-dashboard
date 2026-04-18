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

class AddressReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $rows = [];

    public function __construct($data)
    {
        foreach ($data as $item) {

            $this->rows[] = [

                'address_id'   =>  $item['address_id'],
                'address_name'   =>  $item['address_name'],
                'currency'   =>  $item['currency'],
                'region'   =>  $item['region'],
                'warehouse'   =>  $item['warehouse'],
                'warehouse_man'   =>  $item['warehouse_man'],


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
            'رقم العنوان',
            'العنوان',

            'العملة',
            'المنطقة',
            'المستودع',

            'الموزع',
        ];
    }

    // ✅ Map each row
    public function map($row): array
    {
        return [
            $row['address_id'],
            $row['address_name'],
            $row['currency'],
            $row['region'],
            $row['warehouse'],
            $row['warehouse_man']
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:F')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->setRightToLeft(true);
        // 🔥 Header row styling
        $sheet->getStyle('A1:F1')->applyFromArray([
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
