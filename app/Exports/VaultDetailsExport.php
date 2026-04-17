<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class VaultDetailsExport implements FromCollection, WithHeadings, WithStyles
{
    protected $vault;
    protected $transactions;
    protected $from;
    protected $to;

    public function __construct($vaultReport)
    {
        $this->vault = $vaultReport['vault'];
        $this->transactions = $vaultReport['transactions'];
        $this->from = $vaultReport['from'];
        $this->to = $vaultReport['to'];
    }

    public function collection()
    {
        $rows = [];

        // Vault info
        $rows[] = ['الخزنة', $this->vault['owner']];
        $rows[] = ['الرصيد الحالي', $this->vault['balance']];
        $rows[] = ['الفترة من', $this->from];
        $rows[] = ['إلى', $this->to];
        $rows[] = []; // empty row

        // Headings
        $rows[] = [
            'ID',
            'التاريخ',
            'النوع',
            'المبلغ',
            'اتجاه',
            'الرصيد قبل (من)',
            'الرصيد بعد (من)',
            'الرصيد قبل (إلى)',
            'الرصيد بعد (إلى)',
            'متعلق ب',
            'Reference ID',
            'السبب',
            'ملاحظات',
        ];

        // Data rows
        foreach ($this->transactions as $trx) {
            $rows[] = [
                $trx['id'],
                $trx['date'],
                $trx['type'],
                $trx['amount'],
                $trx['direction'],
                $trx['from_balance_before'],
                $trx['from_balance_after'],
                $trx['to_balance_before'],
                $trx['to_balance_after'],
                $trx['reference_type'],
                $trx['reference_id'],
                $trx['reason'],
                $trx['notes'],
            ];
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return []; // headings are included in collection
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->setRightToLeft(true);

        // Make headings bold
        $sheet->getStyle('A5:M5')->applyFromArray([
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

        // Auto size columns
        foreach (range('A', 'M') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        return [];
    }
}
