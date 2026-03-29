<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VaultExport implements FromArray, WithStyles, ShouldAutoSize
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function array(): array
    {
        $rows = [];

        // ✅ Title
        $rows[] = ['تقرير الخزائن'];
        $rows[] = [];

        // ✅ Period
        $rows[] = ['من', $this->data['from']];
        $rows[] = ['إلى', $this->data['to']];

        $rows[] = [];

        // ✅ Table Header
        $rows[] = ['#', 'اسم المالك', 'الرصيد الحالي', 'إجمالي الداخل', 'إجمالي الخارج'];

        // ✅ Data
        foreach ($this->data['vaults'] as $index => $vault) {
            $rows[] = [
                $index + 1,
                $vault['owner'] ?? 'N/A',
                $vault['balance'],
                $vault['total_in'],
                $vault['total_out'],
            ];
        }

        // ✅ Total row
        $rows[] = [];
        $rows[] = [
            '',
            'إجمالي الأرصدة',
            $this->data['total_balances'],
            '',
            '',
        ];

        return $rows;
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
