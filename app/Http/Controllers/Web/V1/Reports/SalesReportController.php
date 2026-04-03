<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\MarketerDailyExport;
use App\Exports\MarketerOrdersExport;
use App\Exports\SalesReportExport;
use App\Exports\SubTeamMarketersExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Reports\SalesReportRequest;
use App\Services\DashUser\Reports\SalesReprotsService;
use App\Services\DashUser\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class SalesReportController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_sales_reports', only: ['salesReport', 'subTeamMarketersReport', 'marketerDailyReport', 'marketerOrdersReport']),

        ];
    }

    public function __construct(private SalesReprotsService $service) {}

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
        return Excel::download(new SalesReportExport($data, $type), $fileName);
    }

    private function exportPdf($data)
    {
        $html = view('reports.sales', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'sales_' . now()->format('Y-m-d_h:i') . '_report.pdf';
        return $pdf->download($fileName);
    }

    public function subTeamMarketersReport(Request $request)
    {
        $data = $request->validate([
            'sub_team_id' => ['required', 'exists:sub_teams,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $result = $this->service->subTeamMarketersReport($data);

        // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] === 'excel') {
                return $this->exportTeamMarketersExcel($result);
            }

            if ($data['export'] === 'pdf') {
                return $this->exportTeamMarketersPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }
    private function exportTeamMarketersExcel($data)
    {
        $fileName = 'subteam_marketers_' . now()->format('Y-m-d_h:i') . '_report.xlsx';

        return Excel::download(
            new SubTeamMarketersExport($data),
            $fileName
        );
    }
    private function exportTeamMarketersPdf($data)
    {
        $html = view('reports.subteam-marketers', [
            'data' => $data
        ])->render();

        $pdf = LaravelMpdfDz::loadHTML($html);

        $fileName = 'subteam_marketers_' . now()->format('Y-m-d_h:i') . '_report.pdf';

        return $pdf->download($fileName);
    }
    public function marketerDailyReport(Request $request)
    {
        $data = $request->validate([
            'marketer_id' => ['required', 'exists:app_users,id'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $result = $this->service->marketerDailyReport($data);

        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportMarketerDailyExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportMarketerDailyPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }
    private function exportMarketerDailyExcel($data)
    {
        $fileName = 'marketer_daily_' . now()->format('Y-m-d_h:i') . '_report.xlsx';

        return Excel::download(
            new MarketerDailyExport($data),
            $fileName
        );
    }
    private function exportMarketerDailyPdf($data)
    {
        $html = view('reports.marketer-daily', [
            'data' => $data
        ])->render();

        $pdf = LaravelMpdfDz::loadHTML($html);

        $fileName = 'marketer_daily_' . now()->format('Y-m-d_h:i') . '_report.pdf';

        return $pdf->download($fileName);
    }

    public function marketerOrdersReport(Request $request)
    {
        $data = $request->validate([
            'marketer_id' => ['required', 'exists:app_users,id'],
            'date' => ['required', 'date'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $result = $this->service->marketerOrdersDetailedReport($data);

        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportMarketerOrdersExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportMarketerOrdersPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }
    private function exportMarketerOrdersExcel($data)
    {
        $fileName = 'marketer_orders_' . now()->format('Y-m-d_h:i') . '_report.xlsx';

        return Excel::download(
            new MarketerOrdersExport($data),
            $fileName
        );
    }
    private function exportMarketerOrdersPdf($data)
    {
        $html = view('reports.marketer-orders', [
            'data' => $data
        ])->render();

        $pdf = LaravelMpdfDz::loadHTML($html);

        $fileName = 'marketer_orders_' . now()->format('Y-m-d_h:i') . '_report.pdf';

        return $pdf->download($fileName);
    }
}
