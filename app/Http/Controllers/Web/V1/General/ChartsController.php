<?php

namespace App\Http\Controllers\Web\V1\General;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\CustomerOrder;
use App\Models\OrderOffer;
use App\Models\OrderProduct;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;

class ChartsController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_statistics', only: ['main', 'tables', 'lineAndBarCharts']),

        ];
    }

    public function main(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $bestProduct = OrderProduct::select(
            'product_id',
            DB::raw('SUM(quantity) as total_quantity')
        )
            // ->whereHas('order', fn($q) => $q->where('order_status', OrderStatus::completed->value))
            ->groupBy('product_id')
            ->with('product:id,name')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('total_quantity')
            ->first();

        $bestOffer = OrderOffer::select(
            'offer_id',
            DB::raw('SUM(quantity) as total_quantity')
        )
            // ->whereHas('order', fn($q) => $q->where('order_status', OrderStatus::completed->value))
            ->groupBy('offer_id')
            ->with('offer:id,name')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('total_quantity')
            ->first();


        $bestMarketer = CustomerOrder::select(
            'app_user_id',
            DB::raw('COUNT(*) as total_orders')
        )
            ->whereNotNull('app_user_id')
            ->groupBy('app_user_id')
            ->with('marketer:id,first_name,last_name')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('total_orders')
            ->first();


        $bestTeam = CustomerOrder::select(
            'team_id',
            DB::raw('COUNT(*) as total_orders')
        )
            ->whereNotNull('team_id')
            ->groupBy('team_id')
            ->with('team:id,name')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->orderByDesc('total_orders')
            ->first();

        $retuned = [
            'bestProduct' => [
                "id" => $bestProduct?->product?->id,
                "name" => $bestProduct?->product?->name,
                'value' => $bestProduct?->total_quantity
            ],
            'bestOffer' => [
                "id" => $bestOffer?->offer?->id,
                "name" => $bestOffer?->offer?->name,
                'value' => $bestOffer?->total_quantity
            ],
            'bestMarketer' => [
                "id" => $bestMarketer?->marketer?->id,
                "name" => $bestMarketer?->marketer?->first_name . ' ' . $bestMarketer?->marketer?->last_name,
                'value' => $bestMarketer?->total_orders
            ],
            'bestTeam' => [
                "id" => $bestTeam?->team?->id,
                "name" => $bestTeam?->team?->name,
                'total_orders' => $bestTeam?->total_orders
            ]
        ];

        return response()->format($retuned, 'messages.success', 200);
    }


    public function tables(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        $bestProducts = OrderProduct::select(
            'product_id',
            DB::raw('SUM(quantity) as total')
        )
            // ->whereHas('order', fn($q) => $q->where('order_status', OrderStatus::completed->value))
            ->with('product:id,name')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('product_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item?->product?->id,
                'name' => $item?->product?->name,
                'value' => $item?->total
            ]);

        $bestOffers = OrderOffer::select(
            'offer_id',
            DB::raw('SUM(quantity) as total')
        )
            // ->whereHas('order', fn($q) => $q->where('order_status', OrderStatus::completed->value))
            ->with('offer:id,name')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('offer_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($item) => [
                'id' => $item?->offer?->id,
                'name' => $item?->offer?->name,
                'value' => $item?->total
            ]);

        return response()->format([
            'top_products' => $bestProducts,
            'top_offers' => $bestOffers,
        ], 'messages.success');
    }

    public function lineAndBarCharts(Request $request)
    {
        $from = $request->input('from');
        $to = $request->input('to');

        // =========================
        // 📊 Orders by Status
        // =========================
        $ordersByStatus = CustomerOrder::select(
            'order_status',
            DB::raw('COUNT(*) as total')
        )
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('order_status')
            ->get();

        $statusChart = [
            'labels' => $ordersByStatus->pluck('order_status'),
            'data' => $ordersByStatus->pluck('total'),
        ];

        // =========================
        // 📈 Revenue Over Time
        // =========================
        $revenue = CustomerOrder::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price) as total')
        )
            // ->where('order_status', OrderStatus::completed->value)
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $revenueChart = [
            'labels' => $revenue->pluck('date'),
            'data' => $revenue->pluck('total'),
        ];

        // =========================
        // 🌍 Orders by Zone
        // =========================
        $zones = CustomerOrder::select(
            'zone_id',
            DB::raw('COUNT(*) as total')
        )
            ->with('zone:id,name')
            ->when($from, fn($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('zone_id')
            ->get();

        $zoneChart = [
            'labels' => $zones->map(fn($z) => $z->zone?->name),
            'data' => $zones->pluck('total'),
        ];

        return response()->format([
            'orders_by_status' => $statusChart,
            'revenue' => $revenueChart,
            'orders_by_zone' => $zoneChart,
        ], 'messages.success');
    }

    public function financialSummaryReport(Request $request)
    {
        
        $from = $request->input('from');
        $to = $request->input('to');

        $query = CustomerOrder::query()

            // =========================
            // FILTERS
            // =========================
            ->when(
                $from,
                fn($q) =>
                $q->whereDate('created_at', '>=', $from)
            )
            ->when(
                $to,
                fn($q) =>
                $q->whereDate('created_at', '<=', $to)
            )

            // =========================
            // EXCLUDE STATUSES
            // =========================
            ->whereNotIn('order_status', ['cancelled', 'refund']);

        // =========================
        // CALCULATE
        // =========================
        $result = $query->selectRaw("
        SUM(total_price * current_exchange_rate) as total_price_sum,

        SUM(total_base_price * current_exchange_rate) as total_base_price_sum,

        SUM(
            (
                CASE
                    WHEN adjustment_type = 'percentage'
                        THEN total_base_price * (adjustment_value / 100)
                    ELSE adjustment_value
                END
            )
            *
            CASE
                WHEN adjustment_operation = 'decrease' THEN -1
                ELSE 1
            END
            *
            current_exchange_rate
        ) as total_adjustment_sum
    ")->first();

        return response()->format([
            'total_price' => (float) ($result->total_price_sum ?? 0),
            'total_base_price' => (float) ($result->total_base_price_sum ?? 0),
            'total_adjustment' => (float) ($result->total_adjustment_sum ?? 0),
        ], 'messages.success');
    }
}
