<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\TeamReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Reports\SalesReportRequest;
use App\Services\DashUser\ReportsService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class SalesReportController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_sales_reports', only: ['index']),

        ];
    }

    public function __construct(private ReportsService $service) {}

    public function salesReport(SalesReportRequest $request)
    {
        $data = $request->validated();


        if ($data['type'] == 'team') {
            $result = $this->service->teamReport($data);
        } else {
            $result = $this->service->subTeamReport($data);
        }

        // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportExcel($result, $data['type']);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }


    private function exportExcel($data, $type)
    {
        $fileName = 'sales_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new TeamReportExport($data, $type), $fileName);
    }

    private function exportPdf($data)
    {
        $html = view('reports.team', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'sales_' . now()->format('Y-m-d_h:i') . '_report.pdf';
        return $pdf->download($fileName);
    }
}
