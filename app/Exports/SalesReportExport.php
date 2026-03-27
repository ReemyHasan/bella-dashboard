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

class SalesReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $rows = [];
    protected $type;

    public function __construct($data, $type)
    {
        $this->type = $type;

        foreach ($data as $item) {

            if ($type == 'team') {
                $this->rows[] = [
                    'name' => $item['team_name'],
                    'manager' => $item['manager_name'],
                    'orders' => (int) ($item['total_orders'] ?? 0),
                    'sales' => (float) ($item['total_sales'] ?? 0),
                ];
            } else {
                $this->rows[] = [
                    'name' => $item['sub_team_name'],
                    'leader' => $item['team_leader_name'],
                    'orders' => (int) ($item['total_orders'] ?? 0),
                    'sales' => (float) ($item['total_sales'] ?? 0),
                ];
            }
        }
    }

    public function collection()
    {
        return collect($this->rows);
    }

    // ✅ Dynamic Arabic headers
    public function headings(): array
    {
        if ($this->type == 'team') {
            return [
                'اسم الفريق',
                'اسم المدير',
                'عدد الطلبات',
                'إجمالي المبيعات(بالعملة المحلية)',
            ];
        }

        return [
            'اسم الفريق الفرعي',
            'اسم قائد الفريق',
            'عدد الطلبات',
            'إجمالي المبيعات',
        ];
    }

    public function map($row): array
    {
        return [
            $row['name'],
            $row['manager'] ?? $row['leader'],
            $row['orders'],
            $row['sales'],
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // 🔢 Numbers format
        $sheet->getStyle('C:D')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);

        // 📏 Auto width
        foreach (range('A', 'D') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // 🔁 RTL
        $sheet->setRightToLeft(true);

        // 🎨 Header styling
        $sheet->getStyle('A1:D1')->applyFromArray([
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

        return [];
    }
}
