<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\UserLedgerExport;
use App\Http\Controllers\Controller;
use App\Services\DashUser\Reports\UserAccountReportService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class UserReportsController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_users_reports', only: ['ordersReport']),

        ];
    }

    public function __construct(private UserAccountReportService $service) {}

    public function userBalanceLedgerReport(Request $request)
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'user_id' => ['required', 'exists:app_users,id'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $result = $this->service->userBalanceLedger($data);

        if (!empty($data['export'])) {

            if ($data['export'] === 'excel') {
                return $this->exportExcel($result);
            }

            if ($data['export'] === 'pdf') {
                return $this->exportPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }

    private function exportExcel($data)
    {
        $fileName = 'user_balance_ledger_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new UserLedgerExport($data), $fileName);
    }

    private function exportPdf($data)
    {
        $html = view('reports.user-balance-ledger', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'user_balance_ledger_' . now()->format('Y-m-d_h:i') . '_report.pdf';
        return $pdf->download($fileName);
    }

}
