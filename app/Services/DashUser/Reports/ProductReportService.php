<?php

namespace App\Services\DashUser\Reports;

use App\Models\OrderOffer;
use App\Models\OrderProduct;
use App\Models\Product;
use App\Models\ProductZonePrice;
use Illuminate\Support\Facades\DB;

class ProductReportService
{

    public function productZoneReport(array $filters)
    {
        $zoneIds = $filters['zone_ids'] ?? [];

        $query = ProductZonePrice::query()
            ->with([
                'zone:id,name',
                'product:id,name'
            ])
            ->when(
                $filters['from'] ?? null,
                fn($q, $from) => $q->whereDate('created_at', '>=', $from)
            )

            ->when(
                $filters['to'] ?? null,
                fn($q, $to) => $q->whereDate('created_at', '<=', $to)
            )
            ->when(
                !empty($zoneIds),
                fn($q) => $q->whereIn('zone_id', $zoneIds)
            );

        $data = $query->get();
        return $data
            ->groupBy('zone_id')
            ->map(function ($items) {

                $zone = $items->first()->zone;

                return [
                    'zone_id' => $zone?->id,
                    'zone_name' => $zone?->name,

                    'products' => $items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product?->name,
                            'price' => $item->price,
                            'is_available' => $item->is_available,
                            'price_after_adjustment' => $item->price_after_adjustment,

                        ];
                    })->values()
                ];
            })
            ->values();
    }

    public function soldAndStagnantProductsReport(array $filters)
    {
        $from = $filters['from'] ?? null;
        $to   = $filters['to'] ?? null;
        $statusFilter = $filters['status'] ?? null;

        // =========================
        // 1. Direct product sales
        // =========================
        $directSales = OrderProduct::query()
            ->selectRaw('product_id, SUM(quantity) as total_sold')
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                    ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));
            })
            ->groupBy('product_id');

        // =========================
        // 2. Offer-based sales
        // =========================
        $offerSales = OrderOffer::query()
            ->join('offer_products', 'order_offers.offer_id', '=', 'offer_products.offer_id')
            ->selectRaw('offer_products.product_id, SUM(order_offers.quantity * offer_products.quantity) as total_sold')
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
                    ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to));
            })
            ->groupBy('offer_products.product_id');

        // =========================
        // 3. Merge sales
        // =========================
        $sales = DB::query()
            ->fromSub($directSales->unionAll($offerSales), 'sales')
            ->selectRaw('product_id, SUM(total_sold) as total_sold')
            ->groupBy('product_id')
            ->pluck('total_sold', 'product_id');

        // =========================
        // 4. Products
        // =========================
        $products = Product::query()
            ->select('id', 'name')
            ->get();

        // =========================
        // 5. Build report
        // =========================
        $result = $products->map(function ($product) use ($sales) {

            $sold = (int) ($sales[$product->id] ?? 0);

            return [
                'product_id'   => $product->id,
                'product_name' => $product->name,
                'total_sold'   => $sold,
                'status'       => $sold > 0 ? 'sold' : 'stagnant',
            ];
        });

        if ($statusFilter == 'sold') {
            $result = $result->filter(fn($item) => $item['total_sold'] > 0);
        }

        if ($statusFilter == 'stagnant') {
            $result = $result->filter(fn($item) => $item['total_sold'] == 0);
        }

        return $result->values();
    }
}
