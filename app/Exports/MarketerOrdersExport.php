<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class MarketerOrdersExport implements FromCollection, WithHeadings, WithStyles
{
    protected $rows = [];

    public function __construct($data)
    {
        foreach ($data['orders'] as $order) {
            $this->rows[] = [
                $order['order_id'],
                $order['order_number'],
                $order['additional_tips'],
                $order['deduction_amount'],
                $order['deduction_type'],
                $order['price_before_exchange'],
                $order['currency'],
                $order['current_exchange_rate'],
                $order['price_after_exchange'],
                $order['order_status'],
                $order['products'],

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
            'معرف الطلب',
            'الرقم التسلسلي للطلب',
            'الإضافات',
            'الخصم',
            'طريقة الخصم',
            'السعر بالعملة المباعة',
            'العملة',
            'قيمة التحويل',
            'السعر بعد تحويل العملة',
            'حالة الطلب',
            'المنتجات',
        ];
    }
    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        // Header styling
        $sheet->getStyle('A1:K1')->applyFromArray([
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

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
