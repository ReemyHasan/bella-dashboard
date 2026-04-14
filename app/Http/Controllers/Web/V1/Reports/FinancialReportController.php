<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\CashRequestReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Reports\CashRequestReportRequest;
use App\Services\DashUser\Reports\FinancialReportService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class FinancialReportController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            // new Middleware('permission:view_cash_requests_reports', only: ['cashRequestReport']),

        ];
    }

    public function __construct(private FinancialReportService $service) {}

    public function cashRequestReport(CashRequestReportRequest $request)
    {
        $data = $request->validated();


        $result = $this->service->cashRequestReport($data);


        // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportCashRequestExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportCashRequestPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }

    private function exportCashRequestExcel($data)
    {
        $fileName = 'cash_requests_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new CashRequestReportExport($data), $fileName);
    }

    private function exportCashRequestPdf($data)
    {
        $html = view('reports.cash-requests', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'cash_requests_' . now()->format('Y-m-d_h:i') . '_report.pdf';

        return $pdf->download($fileName);
    }
}
