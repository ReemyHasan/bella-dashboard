<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected array $rows = [];
    protected array $warehouses = [];

    public function __construct(array $data)
    {
        $this->warehouses = collect($data['warehouses'] ?? [])
            ->values()
            ->toArray();

        $this->rows = collect($data['rows'] ?? [])
            ->values()
            ->toArray();
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return array_merge(
            ['اسم المنتج'],
            collect($this->warehouses)
                ->pluck('name')
                ->toArray()
        );
    }

    public function map($row): array
    {
        $result = [
            $row['product_name'] ?? '',
        ];

        foreach ($this->warehouses as $warehouse) {

            $key = 'warehouse_' . $warehouse['id'];

            $result[] = (int) ($row[$key] ?? 0);
        }

        return $result;
    }

    public function styles(Worksheet $sheet)
    {
        $totalColumns = count($this->warehouses) + 1;

        $lastColumn = Coordinate::stringFromColumnIndex($totalColumns);

        // Number format for warehouse quantities
        if ($totalColumns > 1) {
            $sheet
                ->getStyle("B:{$lastColumn}")
                ->getNumberFormat()
                ->setFormatCode(NumberFormat::FORMAT_NUMBER);
        }

        // Auto size
        for ($column = 1; $column <= $totalColumns; $column++) {

            $columnLetter = Coordinate::stringFromColumnIndex($column);

            $sheet
                ->getColumnDimension($columnLetter)
                ->setAutoSize(true);
        }

        // RTL
        $sheet->setRightToLeft(true);

        // Header styling
        $sheet
            ->getStyle("A1:{$lastColumn}1")
            ->applyFromArray([
                'font' => [
                    'bold' => true,
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],

                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '4472C4',
                    ],
                ],

                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

        // Center warehouse quantities
        if ($totalColumns > 1) {
            $sheet
                ->getStyle("B:{$lastColumn}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }
}
