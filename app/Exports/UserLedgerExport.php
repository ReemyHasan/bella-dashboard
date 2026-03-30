<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class UserLedgerExport implements FromCollection, WithStyles
{
    protected $ledger;

    public function __construct($ledger)
    {
        $this->ledger = $ledger;
    }

    public function collection()
    {
        $rows = [];

        // user info
        $rows[] = ['المستخدم', $this->ledger['user']['name']];
        $rows[] = ['الرصيد الحالي', $this->ledger['user']['current_balance']];
        $rows[] = ['الفترة من', $this->ledger['from']];
        $rows[] = ['إلى', $this->ledger['to']];
        $rows[] = []; // empty row

        // headings
        $rows[] = ['التاريخ', 'النوع', 'المصدر', 'معرف المرجع', 'المبلغ', 'الرصيد قبل', 'الرصيد بعد', 'السبب', 'ملاحظات'];

        // data
        foreach ($this->ledger['transactions'] as $trx) {
            $rows[] = [
                $trx['date'],
                $trx['type'],
                $trx['reference_type'],
                $trx['reference_id'],
                $trx['amount'],
                $trx['balance_before'],
                $trx['balance_after'],
                $trx['reason'],
                $trx['notes'],
            ];
        }

        return collect($rows);
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);
        $sheet->getStyle('A5:I5')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}