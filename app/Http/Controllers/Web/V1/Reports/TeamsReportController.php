<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\TeamReportExport;
use App\Http\Controllers\Controller;
use App\Services\DashUser\Reports\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class TeamsReportController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            // new Middleware('permission:view_teams_reports', only: ['teamsHierarchyReport']),

        ];
    }

    public function __construct(private ReportsService $service) {}

    public function teamsHierarchyReport(Request $request)
    {
        $data = $request->validate([
            'team_ids' => ['nullable', 'array'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $result = $this->service->teamsHierarchyReport($data);

         // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }

    private function exportExcel($data)
    {
        $fileName = 'teams_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new TeamReportExport($data), $fileName);
    }

    private function exportPdf($data)
    {
        $html = view('reports.team', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'teams_' . now()->format('Y-m-d_h:i') . '_report.pdf';
        return $pdf->download($fileName);
    }
}
