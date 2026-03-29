<?php

namespace App\Services\DashUser\Reports;

use App\Enums\OrderStatus;
use App\Models\AppUser;
use App\Models\CustomerOrder;
use App\Models\SubTeam;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class SalesReprotsService
{


    public function subTeamMarketersReport(array $filters)
    {
        $subTeamName = null;

        if (!empty($filters['sub_team_id'])) {
            $subTeamName = SubTeam::find($filters['sub_team_id'])?->name;
        }
        return CustomerOrder::select(
            'app_user_id',
            DB::raw('SUM(total_price * current_exchange_rate) as total_sales'),
            DB::raw('COUNT(*) as total_orders')
        )
            ->where('order_status', OrderStatus::completed->value)

            ->with([
                'subTeam:id,name',
                'marketer:id,first_name,last_name,subteam_id'
            ])

            // 🔥 filter by sub team (IMPORTANT)
            ->when(
                $filters['sub_team_id'] ?? null,
                fn($q, $id) => $q->where('sub_team_id', $id)
            )

            ->when(
                $filters['from'] ?? null,
                fn($q, $from) => $q->whereDate('created_at', '>=', $from)
            )

            ->when(
                $filters['to'] ?? null,
                fn($q, $to) => $q->whereDate('created_at', '<=', $to)
            )

            ->groupBy('app_user_id')
            ->orderByDesc('total_sales')

            ->get()

            ->map(function ($item) use ($subTeamName) {

                return [
                    'sub_team_name' => $subTeamName,
                    'marketer_id' => $item->app_user_id,
                    'marketer_name' => $item->marketer
                        ? $item->marketer->first_name . ' ' . $item->marketer->last_name
                        : 'N/A',

                    'total_orders' => (int) ($item->total_orders ?? 0),
                    'total_sales'  => (float) ($item->total_sales ?? 0),
                ];
            });
    }
    public function teamReport(array $filters)
    {
        return CustomerOrder::select(
            'team_id',

            DB::raw('SUM(total_price * current_exchange_rate) as total_sales'),

            DB::raw('COUNT(*) as total_orders')
        )
            ->where('order_status', OrderStatus::completed->value)

            ->with([
                'team:id,name,manager_id',
                'team.manager:id,first_name,last_name',
            ])

            ->when(
                $filters['team_id'] ?? null,
                fn($q, $teamId) => $q->where('team_id', $teamId)
            )

            ->when(
                $filters['from'] ?? null,
                fn($q, $from) => $q->whereDate('created_at', '>=', $from)
            )

            ->when(
                $filters['to'] ?? null,
                fn($q, $to) => $q->whereDate('created_at', '<=', $to)
            )

            ->groupBy('team_id', 'currency_id')
            ->orderByDesc('total_sales')
            ->get()
            ->groupBy('team_id') // 🔥 group currencies under team
            ->map(function ($items) {

                $first = $items->first();

                return [
                    'team_name' => $first->team?->name,

                    'manager_name' => $first->team?->manager
                        ? $first->team->manager->first_name . ' ' . $first->team->manager->last_name
                        : "N/A",
                    'total_orders' => $items->sum('total_orders') ?? 0,
                    'total_sales' => $items->sum('total_sales') ?? 0,
                ];
            })
            ->values();
    }

    public function subTeamReport(array $filters)
    {
        return CustomerOrder::select(
            'sub_team_id',
            DB::raw('SUM(total_price * current_exchange_rate) as total_sales'),

            DB::raw('COUNT(*) as total_orders')
        )
            ->where('order_status', OrderStatus::completed->value)
            ->with([
                'subTeam:id,name,team_leader_id,is_direct',
                'subTeam.teamLeader:id,first_name,last_name'
            ])
            ->when(
                $filters['team_id'] ?? null,
                fn($q, $teamId) =>
                $q->whereHas('subTeam', fn($sq) => $sq->where('team_id', $teamId))
            )
            ->when($filters['sub_team_id'] ?? null, fn($q, $id) => $q->where('sub_team_id', $id))
            ->when($filters['from'] ?? null, fn($q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['to'] ?? null, fn($q, $to) => $q->whereDate('created_at', '<=', $to))
            ->groupBy('sub_team_id')
            ->orderByDesc('total_sales')
            ->get()
            ->map(fn($item) => [
                'sub_team_name' => $item->subTeam?->name,
                'is_direct' => $item->subTeam?->is_direct,
                'team_leader_name' => $item->subTeam?->teamLeader
                    ? $item->subTeam?->teamLeader?->first_name . ' ' . $item->subTeam?->teamLeader?->last_name
                    : "N/A",
                'total_sales' => $item->total_sales ?? 0,
                'total_orders' => $item->total_orders ?? 0,
            ]);
    }
    public function marketerDailyReport(array $filters)
    {
        $from = Carbon::parse($filters['from']);
        $to   = Carbon::parse($filters['to']);

        // marketer name
        $marketer = AppUser::find($filters['marketer_id']);

        // 🔥 aggregated data
        $orders = CustomerOrder::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('SUM(total_price * current_exchange_rate) as total_sales'),
            DB::raw('COUNT(*) as total_orders')
        )
            ->where('order_status', OrderStatus::completed->value)
            ->where('app_user_id', $filters['marketer_id'])
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date')
            ->get()
            ->keyBy('date');

        // 🔥 fill missing days
        $period = CarbonPeriod::create($from, $to);

        $data = collect($period)->map(function ($date) use ($orders) {

            $day = $date->format('Y-m-d');

            return [
                'date' => $day,
                'total_orders' => (int) ($orders[$day]->total_orders ?? 0),
                'total_sales'  => (float) ($orders[$day]->total_sales ?? 0),
            ];
        });

        return [
            'marketer_name' => $marketer
                ? $marketer->first_name . ' ' . $marketer->last_name
                : 'N/A',

            'from' => $from->format('Y-m-d'),
            'to'   => $to->format('Y-m-d'),

            'days' => $data,
        ];
    }

    public function marketerOrdersDetailedReport(array $filters)
    {
        $date = Carbon::parse($filters['date'])->startOfDay();
        $endDate = Carbon::parse($filters['date'])->endOfDay();

        $marketer = AppUser::find($filters['marketer_id']);

        $orders = CustomerOrder::with(['products.product', 'currency']) // eager load product relation
            ->where('app_user_id', $filters['marketer_id'])
            ->whereBetween('created_at', [$date, $endDate])
            ->get()
            ->map(function ($order) {

                return [
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,

                    'additional_tips' => $order->additional_tips,
                    'deduction_amount' => $order->deduction_amount,
                    'deduction_type' => $order->deduction_type,

                    'price_before_exchange' => $order->total_price,
                    'currency' => optional($order->currency)->symbol ?? 'N/A',
                    'current_exchange_rate' => $order->current_exchange_rate,

                    'price_after_exchange' => $order->total_price * $order->current_exchange_rate,

                    'order_status' => $order->order_status,

                    // 🔥 products names as string
                    'products' => $order->products
                        ->pluck('product.name')
                        ->filter()
                        ->implode(', '),
                ];
            });

        return [
            'marketer_name' => $marketer
                ? $marketer->first_name . ' ' . $marketer->last_name
                : 'N/A',

            'date' => $date->format('Y-m-d'),

            'total_orders' => $orders->count(),

            'orders' => $orders,
        ];
    }
}
