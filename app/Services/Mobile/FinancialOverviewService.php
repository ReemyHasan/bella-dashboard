<?php

namespace App\Services\Mobile;

use App\Enums\OrderStatus;
use App\Enums\VaultTransactionType;
use App\Models\AppUser;
use App\Models\CustomerOrder;
use App\Models\OrderProduct;
use App\Models\VaultTransaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class FinancialOverviewService
{
    private function applyScopeFilters($query, AppUser $user, array $filters)
    {
        $scope = $filters['scope'] ?? 'mine';
        // mine | subteam | team

        // Normal marketer
        if (
            !$user->hasRole('Team Manager') &&
            !$user->hasRole('Team Leader')
        ) {
            return $query->where('app_user_id', $user->id);
        }

        // Team Manager
        if ($user->hasRole('Team Manager')) {

            if ($scope === 'mine') {
                return $query->where('app_user_id', $user->id);
            }

            if ($scope === 'team') {
                return $query->where('team_id', $user->team_id);
            }

            if ($scope === 'subteam') {

                return $query
                    ->where('team_id', $user->team_id)
                    ->when(
                        isset($filters['sub_team_id']),
                        fn($q) =>
                        $q->where('sub_team_id', $filters['sub_team_id'])
                    );
            }
        }

        // Team Leader
        if ($user->hasRole('Team Leader')) {
            if ($scope === 'mine') {
                return $query->where('app_user_id', $user->id);
            }

            if ($scope === 'subteam') {
                return $query->where('sub_team_id', $user->subteam_id);
            }
        }

        return $query;
    }
    public function userBalanceSummary(array $filters)
    {

        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null;
        $to   = isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : null;

        $user = auth()->user();
        $query = CustomerOrder::query();
        $query = $this->applyScopeFilters($query, $user, $filters);
        $query = $query->where('order_status', OrderStatus::completed->value)
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
            ->when($from && !$to, fn($q) => $q->where('created_at', '>=', $from))
            ->when(!$from && $to, fn($q) => $q->where('created_at', '<=', $to));

        $ordersCount = (clone $query)->count();


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

        $prizesTotal = VaultTransaction::where('balance_user_type', AppUser::class)
            ->where('balance_user_id', $user->id)
            ->where('type', VaultTransactionType::COMPETITION_PRIZE->value)

            ->when($from && $to, fn($q) => $q->whereBetween('transaction_date', [$from, $to]))
            ->when($from && !$to, fn($q) => $q->where('transaction_date', '>=', $from))
            ->when(!$from && $to, fn($q) => $q->where('transaction_date', '<=', $to))

            ->sum('amount');
        return [
            'current_balance' => $user->balance,

            'from' => $from?->format('Y-m-d'),
            'to'   => $to?->format('Y-m-d'),

            'orders' => [
                'orders_count' => $ordersCount,
                'total_base_price' => (float) ($result->total_base_price_sum ?? 0),
                'total_adjustment' => (float) ($result->total_adjustment_sum ?? 0),
            ],
            'prizes_total' => $prizesTotal,

        ];
    }

    public function basePriceOverTime(array $filters)
    {
        $from = isset($filters['from'])
            ? Carbon::parse($filters['from'])->startOfDay()
            : now()->subDays(30)->startOfDay();

        $to = isset($filters['to'])
            ? Carbon::parse($filters['to'])->endOfDay()
            : now()->endOfDay();

        $groupBy = $filters['group_by'] ?? 'daily'; // daily | weekly | monthly

        $user = auth()->user();

        $cacheKey = "chart:base_price:{$user->id}:{$groupBy}:"
            . ($from?->timestamp ?? 'null') . ':'
            . ($to?->timestamp ?? 'null');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($from, $to, $groupBy, $user, $filters) {

            $dateFormat = match ($groupBy) {
                'weekly'  => '%Y-%u',
                'monthly' => '%Y-%m',
                default   => '%Y-%m-%d',
            };

            $query = CustomerOrder::query();
            $query = $this->applyScopeFilters($query, $user, $filters);

            $query = $query->where('order_status', OrderStatus::completed->value)

                ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from, $to]))
                ->when($from && !$to, fn($q) => $q->where('created_at', '>=', $from))
                ->when(!$from && $to, fn($q) => $q->where('created_at', '<=', $to));

            $rows = $query
                ->selectRaw("
                DATE_FORMAT(created_at, '{$dateFormat}') as period,
                SUM(total_base_price * current_exchange_rate) as total
            ")
                ->groupBy('period')
                ->orderBy('period')
                ->get()->keyBy('period');

            // 🔹 build chart-friendly arrays
            $labels = [];
            $values = [];

            $cursor = $from->copy();

            while ($cursor <= $to) {

                $period = match ($groupBy) {

                    'weekly' => $cursor->format('Y-W'),

                    'monthly' => $cursor->format('Y-m'),

                    default => $cursor->format('Y-m-d'),
                };

                $labels[] = $this->formatLabel(
                    $period,
                    $groupBy
                );

                $values[] = (float) optional(
                    $rows->get($period)
                )->total ?? 0;

                match ($groupBy) {

                    'weekly' => $cursor->addWeek(),

                    'monthly' => $cursor->addMonth(),

                    default => $cursor->addDay(),
                };
            }

            return [
                'group_by' => $groupBy,
                'labels'   => $labels,
                'values'   => $values,
            ];
        });
    }
    private function formatLabel($period, $groupBy)
    {
        return match ($groupBy) {

            'weekly'  => "Week " . substr($period, 5) . " / " . substr($period, 0, 4),

            'monthly' => Carbon::createFromFormat('Y-m', $period)->format('M Y'),

            default   => Carbon::parse($period)->format('Y-m-d'),
        };
    }

    public function topProducts(array $filters)
    {
        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null;
        $to   = isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : null;

        $user = auth()->user();

        $cacheKey = "top_products:{$user->id}:"
            . ($from?->timestamp ?? 'null') . ':'
            . ($to?->timestamp ?? 'null');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($from, $to, $user, $filters) {

            $rows =  OrderProduct::query()
                ->select([
                    'products.id',
                    'products.name',
                    DB::raw('SUM(order_products.quantity) as total_quantity')
                ])
                ->join('customer_orders', 'customer_orders.id', '=', 'order_products.customer_order_id')
                ->join('products', 'products.id', '=', 'order_products.product_id');
            $rows = $this->applyScopeFilters($rows, $user, $filters);


            $rows = $rows->where('customer_orders.order_status', OrderStatus::completed->value)

                ->when($from && $to, fn($q) => $q->whereBetween('customer_orders.created_at', [$from, $to]))
                ->when($from && !$to, fn($q) => $q->where('customer_orders.created_at', '>=', $from))
                ->when(!$from && $to, fn($q) => $q->where('customer_orders.created_at', '<=', $to))

                ->groupBy('products.id', 'products.name')
                ->orderByDesc('total_quantity')
                ->limit(5)
                ->get();
            return [
                'labels' => $rows->pluck('name')->values(),
                'values' => $rows->pluck('total_quantity')->map(fn($v) => (int) $v)->values(),

                // optional detailed list
                'items' => $rows->map(fn($row) => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'total_quantity' => (int) $row->total_quantity,
                ])->values()
            ];
        });
    }
    public function topCustomers(array $filters)
    {
        $from = isset($filters['from']) ? Carbon::parse($filters['from'])->startOfDay() : null;
        $to   = isset($filters['to']) ? Carbon::parse($filters['to'])->endOfDay() : null;

        $user = auth()->user();

        $cacheKey = "top_customers:{$user->id}:"
            . ($from?->timestamp ?? 'null') . ':'
            . ($to?->timestamp ?? 'null');

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($from, $to, $user, $filters) {

            $rows = CustomerOrder::query()
                ->select([
                    'customers.id',
                    DB::raw("CONCAT(customers.first_name, ' ', customers.last_name) as name"),
                    DB::raw('SUM(customer_orders.total_base_price * customer_orders.current_exchange_rate) as total_spent')
                ])
                ->join('customers', 'customers.id', '=', 'customer_orders.customer_id');

            $rows = $this->applyScopeFilters($rows, $user, $filters);

            // ->where('customer_orders.app_user_id', $user->id)
            $rows = $rows->where('customer_orders.order_status', OrderStatus::completed->value)

                ->when($from && $to, fn($q) => $q->whereBetween('customer_orders.created_at', [$from, $to]))
                ->when($from && !$to, fn($q) => $q->where('customer_orders.created_at', '>=', $from))
                ->when(!$from && $to, fn($q) => $q->where('customer_orders.created_at', '<=', $to))

                ->groupBy('customers.id', 'customers.first_name', 'customers.last_name')
                ->orderByDesc('total_spent')
                ->limit(5)
                ->get();
            return [
                'labels' => $rows->pluck('name')->values(),
                'values' => $rows->pluck('total_spent')->map(fn($v) => (float) $v)->values(),

                'items' => $rows->map(fn($row) => [
                    'id' => $row->id,
                    'name' => $row->name,
                    'total_spent' => (float) $row->total_spent,
                ])->values()
            ];
        });
    }
}
