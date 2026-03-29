<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\OrdersItemExport;
use App\Exports\OrdersReportExport;
use App\Exports\OrdersWarehouseManExport;
use App\Http\Controllers\Controller;
use App\Services\DashUser\Reports\ReportsService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class OrderReportsController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            // new Middleware('permission:view_orders_reports', only: ['ordersReport', 'ordersWarehouseManReport' , 'itemsReport']),

        ];
    }

    public function __construct(private ReportsService $service) {}

    public function ordersReport(Request $request)
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $result = $this->service->ordersDailyReport($data);

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
        $fileName = 'orders_warehouse_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new OrdersReportExport($data), $fileName);
    }

    private function exportPdf($data)
    {
        $html = view('reports.orders', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'orders_warehouse_' . now()->format('Y-m-d_h:i') . '_report.pdf';
        return $pdf->download($fileName);
    }

    public function ordersWarehouseManReport(Request $request)
    {
        $data = $request->validate([
            'day' => ['required', 'date'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $result = $this->service->ordersWarehouseManReport($data);

        // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportWarehouseManExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportWarehouseManPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }

    private function exportWarehouseManExcel($data)
    {
        $fileName = 'orders_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new OrdersWarehouseManExport($data), $fileName);
    }

    private function exportWarehouseManPdf($data)
    {
        $html = view('reports.warehouse_man_orders', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'orders_' . now()->format('Y-m-d_h:i') . '_report.pdf';
        return $pdf->download($fileName);
    }

    public function itemsReport(Request $request)
    {
        $data = $request->validate([
            'from' => ['required', 'date'],
            'to' => ['required', 'date'],
            'type' => ['required', 'in:products,offers'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $result = $data['type'] == 'products'
            ? $this->service->productsOrdersReport($data)
            : $this->service->offersOrdersReport($data);

        if (!empty($data['export'])) {

            if ($data['export'] === 'excel') {
                return $this->exportItemExcel($result, $data['type']);
            }

            if ($data['export'] === 'pdf') {
                return $this->exportItemPdf($result, $data['type']);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }

    private function exportItemExcel($data, $type)
    {
        $fileName = $type . '_report_' . now()->format('Y-m-d_h:i') . '.xlsx';
        return Excel::download(new OrdersItemExport($data), $fileName);
    }

    private function exportItemPdf($data, $type)
    {
        $html = view('reports.items', [
            'data' => $data,
            'type' => $type,
            'from' => request('from'),
            'to' => request('to'),
        ])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = $type . '_report_' . now()->format('Y-m-d_h:i') . '.pdf';
        return $pdf->download($fileName);
    }
}
