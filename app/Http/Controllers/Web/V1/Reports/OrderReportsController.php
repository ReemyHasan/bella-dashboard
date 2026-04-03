<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\OrdersItemExport;
use App\Exports\OrdersReportExport;
use App\Exports\OrdersWarehouseManExport;
use App\Http\Controllers\Controller;
use App\Models\AppUser;
use App\Models\SubTeam;
use App\Models\Team;
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
            new Middleware('permission:view_orders_reports', only: ['ordersReport', 'ordersWarehouseManReport' , 'itemsReport']),

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
            'team_id' => ['nullable', 'exists:teams,id'],
            'sub_team_id' => ['nullable', 'exists:sub_teams,id'],
            'marketer_id' => ['nullable', 'exists:app_users,id'],
            'export' => ['nullable', 'in:excel,pdf'],
        ]);

        $filtersInfo = [
            'team' => !empty($data['team_id'])
                ? optional(Team::find($data['team_id']))->name
                : null,

            'sub_team' => !empty($data['sub_team_id'])
                ? optional(SubTeam::find($data['sub_team_id']))->name
                : null,

            'marketer' => !empty($data['marketer_id'])
                ? optional(AppUser::find($data['marketer_id']))->first_name . ' ' .
                optional(AppUser::find($data['marketer_id']))->last_name
                : null,
        ];

        $result = $data['type'] == 'products'
            ? $this->service->productsOrdersReport($data)
            : $this->service->offersOrdersReport($data);

        $payload = [
            'data' => $result,
            'filters' => $filtersInfo,
            'from' => $data['from'],
            'to' => $data['to'],
        ];
        if (!empty($data['export'])) {

            if ($data['export'] === 'excel') {
                return $this->exportItemExcel($payload, $data['type']);
            }

            if ($data['export'] === 'pdf') {
                return $this->exportItemPdf($payload, $data['type']);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }

    private function exportItemExcel($data, $type)
    {
        $fileName = $type . '_report_' . now()->format('Y-m-d_h:i') . '.xlsx';
        return Excel::download(new OrdersItemExport($data['data']), $fileName);
    }

    private function exportItemPdf($data, $type)
    {
        $html = view('reports.items', [
            'data' => $data['data'],
            'filters' => $data['filters'],
            'type' => $type,
            'from' => request('from'),
            'to' => request('to'),
        ])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = $type . '_report_' . now()->format('Y-m-d_h:i') . '.pdf';
        return $pdf->download($fileName);
    }
}
