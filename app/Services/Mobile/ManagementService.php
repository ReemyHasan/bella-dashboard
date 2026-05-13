<?php

namespace App\Services\Mobile;

use App\Enums\OrderStatus;
use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\CustomerOrder;
use App\Models\SubTeam;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ManagementService
{
    public function appUsers($request)
    {
        $user = auth()->user();

        if (
            !$user->hasRole('Team Manager') &&
            !$user->hasRole('Team Leader')
        ) {
            throw new CustomException('غير مسموح بعرض المستخدمين');
        }

        $query = AppUser::query()
            ->with(['roles', 'createdByAppUser', 'createdByDashUser', 'team', 'subTeam.team'])

            ->when(
                $user->hasRole('Team Manager'),
                fn(Builder $q) =>
                $q->where('team_id', $user->team_id)
            )

            ->when(
                $user->hasRole('Team Leader'),
                fn(Builder $q) =>
                $q->where('subteam_id', $user->subteam_id)
            )

            ->filterBy($request->all())
            ->sortBy(
                $request->get('sort', ['created_at' => 'desc'])
            )
            ->latest();

        return $query->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function showAppUser($id)
    {
        $user = auth()->user();

        if (
            !$user->hasRole('Team Manager') &&
            !$user->hasRole('Team Leader')
        ) {
            throw new CustomException('غير مسموح بعرض المستخدم');
        }

        $appUser = AppUser::query()
            ->with(['roles', 'permissions', 'addresses', 'createdByAppUser', 'createdByDashUser', 'team', 'subTeam.team', 'warehouse'])
            ->when(
                $user->hasRole('Team Manager'),
                fn(Builder $q) =>
                $q->where('team_id', $user->team_id)
            )

            ->when(
                $user->hasRole('Team Leader'),
                fn(Builder $q) =>
                $q->where('subteam_id', $user->subteam_id)
            )->find($id);

        if (!$appUser) {
            throw new CustomException('غير مسموح بعرض المستخدم');
        }

        return $appUser;
    }

    public function marketersSales(array $filters)
    {
        $user = auth()->user();
        $query = CustomerOrder::query()
            ->select(
                'app_user_id',
                'team_id',
                'sub_team_id',
                DB::raw('SUM(total_price * current_exchange_rate) as total_sales'),
                DB::raw('COUNT(*) as total_orders')
            )

            ->where('order_status', OrderStatus::completed->value)

            ->with([
                'team:id,name',
                'subTeam:id,name,team_id',
                'marketer:id,first_name,last_name,team_id,subteam_id'
            ]);

        if ($user->hasRole('Team Manager')) {

            $query->where('team_id', $user->team_id);

            if (!empty($filters['sub_team_id'])) {

                $subteam = SubTeam::query()
                    ->where('team_id', $user->team_id)
                    ->find($filters['sub_team_id']);

                if (!$subteam) {
                    throw new CustomException(
                        'الفريق الفرعي لا يتبع لفريقك'
                    );
                }

                $query->where('sub_team_id', $subteam->id);
            }
        } elseif ($user->hasRole('Team Leader')) {

            $query->where('sub_team_id', $user->subteam_id);
        } else {

            throw new CustomException(
                'غير مسموح بعرض تقارير المندوبين'
            );
        }

        $query
            ->when(
                $filters['from'] ?? null,
                fn($q, $from) =>
                $q->whereDate('created_at', '>=', $from)
            )

            ->when(
                $filters['to'] ?? null,
                fn($q, $to) =>
                $q->whereDate('created_at', '<=', $to)
            );


        $rows = $query
            ->groupBy(
                'app_user_id',
                'team_id',
                'sub_team_id'
            )
            ->orderByDesc('total_sales')
            ->get();

        $overallOrders = (int) $rows->sum('total_orders');

        $overallSales = (float) $rows->sum('total_sales');

        return [

            'summary' => [

                'total_marketers' => $rows->count(),

                'total_orders' => $overallOrders,

                'total_sales' => $overallSales,
            ],

            'items' => $rows->map(function ($item) {

                return [

                    'marketer_id' => $item->app_user_id,

                    'marketer_name' => $item->marketer
                        ? $item->marketer->first_name . ' ' . $item->marketer->last_name
                        : 'N/A',

                    'team_name' => $item->team?->name,

                    'sub_team_name' => $item->subTeam?->name,

                    'total_orders' => (int) ($item->total_orders ?? 0),

                    'total_sales' => round(
                        (float) ($item->total_sales ?? 0),
                        2
                    ),
                ];
            })->values(),
        ];
    }

    public function subteamsSales(array $filters)
    {
        $user = auth()->user();

        if (!$user->hasRole('Team Manager')) {

            throw new CustomException(
                'غير مسموح بعرض تقارير الفرق الفرعية'
            );
        }

        $query = CustomerOrder::query()

            ->select(
                'sub_team_id',

                DB::raw('SUM(total_price * current_exchange_rate) as total_sales'),

                DB::raw('COUNT(*) as total_orders')
            )

            ->where('order_status', OrderStatus::completed->value)

            ->where('team_id', $user->team_id)

            ->with([
                'subTeam:id,name,team_id'
            ]);

        $query
            ->when(
                $filters['from'] ?? null,
                fn($q, $from) =>
                $q->whereDate('created_at', '>=', $from)
            )

            ->when(
                $filters['to'] ?? null,
                fn($q, $to) =>
                $q->whereDate('created_at', '<=', $to)
            );

        $rows = $query
            ->groupBy('sub_team_id')
            ->orderByDesc('total_sales')
            ->get();

        $overallOrders = (int) $rows->sum('total_orders');

        $overallSales = (float) $rows->sum('total_sales');

        return [

            'summary' => [

                'total_subteams' => $rows->count(),

                'total_orders' => $overallOrders,

                'total_sales' => round($overallSales, 2),
            ],

            'items' => $rows->map(function ($item) {

                return [

                    'sub_team_id' => $item->sub_team_id,

                    'sub_team_name' => $item->subTeam?->name,

                    'total_orders' => (int) ($item->total_orders ?? 0),

                    'total_sales' => round(
                        (float) ($item->total_sales ?? 0),
                        2
                    ),
                ];
            })->values(),
        ];
    }
}
