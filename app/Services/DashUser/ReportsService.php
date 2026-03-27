<?php

namespace App\Services\DashUser;

use App\Enums\OrderStatus;
use App\Models\CustomerOrder;
use App\Models\OrderOffer;
use App\Models\OrderProduct;
use App\Models\ProductWarehouse;
use App\Models\Team;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;

class ReportsService
{

    public function warehouseReport(array $filters)
    {
        $warehouseIds = $filters['warehouse_ids'] ?? [];

        $query = ProductWarehouse::query()
            ->with([
                'warehouse:id,name',
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
                !empty($warehouseIds),
                fn($q) => $q->whereIn('warehouse_id', $warehouseIds)
            );

        $data = $query->get();

        // =========================
        // 🔥 FORMAT RESPONSE
        // =========================
        return $data
            ->groupBy('warehouse_id')
            ->map(function ($items) {

                $warehouse = $items->first()->warehouse;

                return [
                    'warehouse_id' => $warehouse?->id,
                    'warehouse_name' => $warehouse?->name,

                    'products' => $items->map(function ($item) {
                        return [
                            'product_id' => $item->product_id,
                            'product_name' => $item->product?->name,

                            'quantity' => (int) ($item->quantity ?? 0),
                            'reserved_quantity' => (int) ($item->reserved_quantity ?? 0),
                            'available' => (int) ($item->available ?? 0), // accessor
                        ];
                    })->values(),

                    'totals' => [
                        'total_quantity' => $items->sum('quantity') ?? 0,
                        'total_reserved' => $items->sum('reserved_quantity') ?? 0,
                        'total_available' => $items->sum(fn($i) => $i->available) ?? 0,
                    ]
                ];
            })
            ->values();
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
                'subTeam:id,name,team_leader_id',
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
                'team_leader_name' => $item->subTeam?->teamLeader
                    ? $item->subTeam?->teamLeader?->first_name . ' ' . $item->subTeam?->teamLeader?->last_name
                    : "N/A",
                'total_sales' => $item->total_sales ?? 0,
                'total_orders' => $item->total_orders ?? 0,
            ]);
    }

    public function teamsHierarchyReport(array $filters)
    {
        return Team::query()
            ->with([
                'manager:id,first_name,last_name',

                'directSubTeams.teamLeader:id,first_name,last_name',
                'directSubTeams.users:id,first_name,last_name,subteam_id',

                'normalSubTeams.teamLeader:id,first_name,last_name',
                'normalSubTeams.users:id,first_name,last_name,subteam_id',
            ])
            ->when(
                $filters['team_ids'] ?? null,
                fn($q, $ids) => $q->whereIn('id', $ids)
            )
            ->get()
            ->map(function ($team) {

                return [
                    'team_name' => $team->name,

                    'manager_name' => $team->manager
                        ? $team->manager->first_name . ' ' . $team->manager->last_name
                        : 'N/A',

                    // 🔥 Direct team
                    'direct_team' => $team->directSubTeams->map(function ($sub) {
                        return [
                            'sub_team_name' => $sub->name,
                            'leader' => $sub->teamLeader
                                ? $sub->teamLeader->first_name . ' ' . $sub->teamLeader->last_name
                                : 'N/A',

                            'users' => $sub->users->map(fn($u) => [
                                'name' => $u->first_name . ' ' . $u->last_name
                            ])
                        ];
                    }),

                    // 🔥 Other subteams
                    'sub_teams' => $team->normalSubTeams->map(function ($sub) {
                        return [
                            'sub_team_name' => $sub->name,
                            'leader' => $sub->teamLeader
                                ? $sub->teamLeader->first_name . ' ' . $sub->teamLeader->last_name
                                : 'N/A',

                            'users' => $sub->users->map(fn($u) => [
                                'name' => $u->first_name . ' ' . $u->last_name
                            ])
                        ];
                    }),
                ];
            });
    }

    public function ordersDailyReport(array $filters)
    {
        $from = Carbon::parse($filters['from']);
        $to   = Carbon::parse($filters['to']);

        $orders = CustomerOrder::select(
            DB::raw('DATE(created_at) as date'),
            'order_status',
            DB::raw('COUNT(*) as total')
        )
            ->whereBetween('created_at', [$from, $to])
            ->groupBy('date', 'order_status')
            ->get();

        $period = CarbonPeriod::create($from, $to);

        $statuses = [
            'pending',
            'approved',
            'completed',
            'cancelled',
            'rejected',
            'refund',
        ];

        // 🔥 Format result
        return collect($period)->map(function ($date) use ($orders, $statuses) {

            $dayData = [
                'date' => $date->format('Y-m-d'),
            ];

            foreach ($statuses as $status) {
                $count = $orders
                    ->where('date', $date->format('Y-m-d'))
                    ->where('order_status', $status)
                    ->sum('total');

                $dayData[$status] = (int) $count;
            }

            return $dayData;
        });
    }

    public function ordersWarehouseManReport(array $filters)
    {
        $day = Carbon::parse($filters['day']);

        $orders = CustomerOrder::with([
            'warehouseMan:id,first_name,last_name'
        ])
            ->whereDate('created_at', $day)
            ->get();


        $dayOrders = $orders->whereBetween('created_at', [
            $day->copy()->startOfDay(),
            $day->copy()->endOfDay()
        ]);

        $grouped = $dayOrders->groupBy('warehouse_man_id');

        return [
            'date' => $day->format('Y-m-d'),

            'warehouse_men' => $grouped->map(function ($items) {

                $first = $items->first();

                return [
                    'name' => $first->warehouseMan
                        ? $first->warehouseMan->first_name . ' ' . $first->warehouseMan->last_name
                        : 'N/A',

                    'orders_count' => $items->count() ?? 0,

                    // 🔥 Total Price
                    'total_price' => $items->sum(function ($o) {
                        return $o->total_price * $o->current_exchange_rate;
                    }),

                    // 🔥 Deduction
                    'total_deduction' => $items->sum(function ($o) {

                        if ($o->deduction_type == 'percentage') {
                            $value = ($o->total_price * $o->deduction_amount) / 100;
                        } else {
                            $value = $o->deduction_amount;
                        }

                        return $value * $o->current_exchange_rate;
                    }),

                    // 🔥 Tips
                    'total_tips' => $items->sum(function ($o) {
                        return $o->additional_tips * $o->current_exchange_rate;
                    }),
                ];
            })->values()
        ];
    }

    public function productsOrdersReport(array $filters)
    {
        return OrderProduct::query()
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->with('product:id,name')
            ->whereHas('order', function ($q) use ($filters) {
                $q->whereBetween('created_at', [
                    $filters['from'],
                    $filters['to']
                ]);
            })
            ->groupBy('product_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->product?->name ?? 'N/A',
                    'quantity' => (int) $item->total_quantity,
                ];
            });
    }
    public function offersOrdersReport(array $filters)
    {
        return OrderOffer::query()
            ->select(
                'offer_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
            ->with('offer:id,name')
            ->whereHas('order', function ($q) use ($filters) {
                $q->whereBetween('created_at', [
                    $filters['from'],
                    $filters['to']
                ]);
            })
            ->groupBy('offer_id')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->offer?->name ?? 'N/A',
                    'quantity' => (int) $item->total_quantity,
                ];
            });
    }
}
