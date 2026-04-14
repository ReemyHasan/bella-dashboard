<?php

namespace App\Http\Controllers\Web\V1\Reports;

use App\Exports\ProductZoneReportExport;
use App\Exports\SoldAndStagnantProductsReportExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Reports\ProductZoneReportRequest;
use App\Http\Requests\DashUser\Reports\SoldAndStagnantProductsReportRequest;
use App\Services\DashUser\Reports\ProductReportService;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Maatwebsite\Excel\Facades\Excel;
use Rezgui\LaravelMpdfDz\Facades\LaravelMpdfDz;

class ProductReportsController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_products_reports', only: ['productZoneReport', 'soldAndStagnantProductsReport']),

        ];
    }

    public function __construct(private ProductReportService $service) {}

    public function productZoneReport(ProductZoneReportRequest $request)
    {
        $data = $request->validated();


        $result = $this->service->productZoneReport($data);


        // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportProductZoneExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportProductZonePdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }
    private function transformForExport($data)
    {
        return collect($data)->flatMap(function ($zone) {

            return collect($zone['products'])->map(function ($product) use ($zone) {
                return [
                    'product' => $product['product_name'],
                    'zone' => $zone['zone_name'],
                    'price' => $product['price'],
                    'is_available' => $product['is_available'],
                    'price_after_adjustment' => $product['price_after_adjustment'],
                ];
            });
        });
    }
    private function exportProductZoneExcel($data)
    {
        $fileName = 'product_zone_prices_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new ProductZoneReportExport($this->transformForExport($data)), $fileName);
    }

    private function exportProductZonePdf($data)
    {
        $html = view('reports.product-zones', ['data' => $this->transformForExport($data)])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'product_zone_prices_' . now()->format('Y-m-d_h:i') . '_report.pdf';

        return $pdf->download($fileName);
    }


    public function soldAndStagnantProductsReport(SoldAndStagnantProductsReportRequest $request)
    {
        $data = $request->validated();


        $result = $this->service->soldAndStagnantProductsReport($data);


        // =========================
        // EXPORT
        // =========================
        if (!empty($data['export'])) {

            if ($data['export'] == 'excel') {
                return $this->exportSoldAndStagnantProductExcel($result);
            }

            if ($data['export'] == 'pdf') {
                return $this->exportSoldAndStagnantProductPdf($result);
            }
        }

        return response()->format($result, 'تم جلب التقرير بنجاح', 200);
    }

    private function exportSoldAndStagnantProductExcel($data)
    {
        $fileName = 'sold_stagnant_products_' . now()->format('Y-m-d_h:i') . '_report.xlsx';
        return Excel::download(new SoldAndStagnantProductsReportExport($data), $fileName);
    }

    private function exportSoldAndStagnantProductPdf($data)
    {
        $html = view('reports.sold-stagnant-products', ['data' => $data])->render();
        $pdf = LaravelMpdfDz::loadHTML($html);
        $fileName = 'sold_stagnant_products_' . now()->format('Y-m-d_h:i') . '_report.pdf';

        return $pdf->download($fileName);
    }
}
