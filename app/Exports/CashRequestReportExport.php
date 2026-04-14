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

class CashRequestReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $rows = [];

    public function __construct($data)
    {
        // 🔥 Flatten data
        // dd($data);
        foreach ($data as $item) {

            $this->rows[] = [
                'id'   =>  $item['id'],
                'payment_method'   =>  $item['payment_method'],
                'requested_for'   =>  $item['requested_for'],
                'mobile'   =>  $item['mobile'],
                'team'   =>  $item['team'],
                'subteam'   =>  $item['subteam'],
                'notes'   =>  $item['notes'],
                'delivered_by'   =>  $item['delivered_by'],
                'requested_amount'   =>  $item['requested_amount'],
                'approved_amount'   =>  $item['approved_amount'],
                'from_vault_balance'   =>  $item['from_vault_balance'],
                'status'   =>  $item['status'],
                'created_at'   =>  $item['created_at']

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
            'رقم الطلب',
            'طريقة الدفع',
            'المسوق',
            'الموبايل',
            'التبعية',
            'ملاحظات المسوق',
            'الموزع',
            'المبلغ المطلوب',
            'المبلغ الموافق عليه',
            'رصيد خزنة المسوق الحالة',
            'الحالة',
            'التاريخ',
        ];
    }

    // ✅ Map each row
    public function map($row): array
    {
        return [
            $row['id'],
            $row['payment_method'],
            $row['requested_for'],
            $row['mobile'],
            $row['team'] . '-' . $row['subteam'],
            $row['notes'],
            $row['delivered_by'],
            $row['requested_amount'],
            $row['approved_amount'],
            $row['from_vault_balance'],

            $row['status'],
            $row['created_at'],
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A:L')
            ->getNumberFormat()
            ->setFormatCode(NumberFormat::FORMAT_NUMBER);
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->setRightToLeft(true);
        // 🔥 Header row styling
        $sheet->getStyle('A1:L1')->applyFromArray([
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
