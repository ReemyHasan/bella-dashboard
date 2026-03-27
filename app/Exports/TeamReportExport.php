<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class TeamReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $rows = [];

    public function __construct($data)
    {
        foreach ($data as $team) {

            // Direct teams
            foreach ($team['direct_team'] as $sub) {
                foreach ($sub['users'] as $user) {

                    $this->rows[] = [
                        'team' => $team['team_name'],
                        'manager' => $team['manager_name'],
                        'type' => 'فريق مباشر',
                        'subteam' => $sub['sub_team_name'],
                        'leader' => $sub['leader'],
                        'user' => $user['name'],
                    ];
                }
            }

            // Sub teams
            foreach ($team['sub_teams'] as $sub) {
                foreach ($sub['users'] as $user) {

                    $this->rows[] = [
                        'team' => $team['team_name'],
                        'manager' => $team['manager_name'],
                        'type' => 'فريق فرعي',
                        'subteam' => $sub['sub_team_name'],
                        'leader' => $sub['leader'],
                        'user' => $user['name'],
                    ];
                }
            }
        }
    }

    public function collection()
    {
        return collect($this->rows);
    }

    public function headings(): array
    {
        return [
            'اسم الفريق',
            'المدير',
            'النوع',
            'الفريق',
            'القائد',
            'المستخدم',
        ];
    }

    public function map($row): array
    {
        return [
            $row['team'],
            $row['manager'],
            $row['type'],
            $row['subteam'],
            $row['leader'],
            $row['user'],
        ];
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