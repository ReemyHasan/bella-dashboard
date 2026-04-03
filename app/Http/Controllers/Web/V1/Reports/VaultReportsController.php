<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\VaultDetailsExport;
use App\Exports\VaultExport;
use App\Http\Controllers\Controller;
use App\Services\DashUser\Reports\VaultReportService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class VaultReportsController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_vault_reports', only: ['vaultReport', 'vaultDetails']),

        ];
    }

    public function __construct(private VaultReportService $service) {}
    public function vaultReport(Request $request)
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'export' => ['nullable', 'in:excel,pdf'],

        ]);

        $result = $this->service->vaultsSummaryReport($data);

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

        return response()->json($result);
    }

    public function vaultDetails(Request $request)
    {
        $data = $request->validate([
            'vault_id' => ['required', 'exists:vaults,id'],
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'export' => ['nullable', 'in:excel,pdf'],

        ]);

        $result = $this->service->vaultDetailsReport($data);

        // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportDetailsExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportDetailsPdf($result);
            }
        }
        return response()->json($result);
    }

    private function exportExcel($data)
    {
        $fileName = 'vault_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new VaultExport($data), $fileName);
    }

    private function exportPdf($data)
    {
        $html = view('reports.vault', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'vault_' . now()->format('Y-m-d_h:i') . '_report.pdf';
        return $pdf->download($fileName);
    }

    private function exportDetailsExcel($data)
    {
        $fileName = 'vault_details_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new VaultDetailsExport($data), $fileName);
    }

    private function exportDetailsPdf($data)
    {
        $html = view('reports.vault-details', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'vault_details_' . now()->format('Y-m-d_h:i') . '_report.pdf';
        return $pdf->download($fileName);
    }
}
