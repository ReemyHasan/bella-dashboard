<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\WarehouseReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Reports\WarehouseReportRequest;
use App\Services\DashUser\Reports\ReportsService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class WarehouseReportController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_warehouses_reports', only: ['index']),

        ];
    }

    public function __construct(private ReportsService $service) {}

    public function warehouseReport(WarehouseReportRequest $request)
    {
        $data = $request->validated();


        $result = $this->service->warehouseReport($data);


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


    private function transformForExport($data)
    {
        return collect($data)->flatMap(function ($warehouse) {

            return collect($warehouse['products'])->map(function ($product) use ($warehouse) {
                return [
                    'product' => $product['product_name'],
                    'warehouse' => $warehouse['warehouse_name'],
                    'quantity'  => (int) ($product['quantity'] ?? 0),
                    'reserved'  => (int) ($product['reserved_quantity'] ?? 0),
                    'available' => (int) ($product['available'] ?? 0),
                ];
            });
        });
    }
    private function exportExcel($data)
    {
        $fileName = 'warehouse_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new WarehouseReportExport($this->transformForExport($data)), $fileName);
    }

    private function exportPdf($data)
    {
        $html = view('reports.warehouse', ['data' => $this->transformForExport($data)])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'warehouse_' . now()->format('Y-m-d_h:i') . '_report.pdf';

        return $pdf->download($fileName);
    }
}
