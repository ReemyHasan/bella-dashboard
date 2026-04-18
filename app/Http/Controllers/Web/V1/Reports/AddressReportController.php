<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\AddressReportExport;
use App\Http\Controllers\Controller;
use App\Services\DashUser\Reports\AddressReportService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Request;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;
use App\Http\Requests\DashUser\Reports\AddressReportRequest;
class AddressReportController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            // new Middleware('permission:view_addresses_reports', only: ['addressesReport']),

        ];
    }

    public function __construct(private AddressReportService $service) {}

    public function addressesReport(AddressReportRequest $request)
    {
        $data = $request->validated();


        $result = $this->service->addressesReport($data);


        // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportAddressExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportAddressPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }

    private function exportAddressExcel($data)
    {
        $fileName = 'addresses_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new AddressReportExport($data), $fileName);
    }

    private function exportAddressPdf($data)
    {
        $html = view('reports.addresses', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'addresses_' . now()->format('Y-m-d_h:i') . '_report.pdf';

        return $pdf->download($fileName);
    }
}
