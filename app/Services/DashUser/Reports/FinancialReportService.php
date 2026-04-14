<?php

namespace App\Services\DashUser\Reports;

use App\Models\CashRequest;

class FinancialReportService
{
    public function cashRequestReport(array $filters)
    {
        $query = CashRequest::query()
            ->with([
                'paymentMethod:id,name_en,name_ar',
                'fromVault:id,balance',
                'requestedFor:id,first_name,last_name,user_name,mobile,team_id,subteam_id',
                'requestedFor.team:id,name',
                'requestedFor.subTeam',
                'requestedFor.subTeam.team:id,name',
                'deliveredBy:id,first_name,last_name,user_name,mobile,team_id,subteam_id',
            ])

            // =========================
            // DATE FILTER
            // =========================
            ->when($filters['from'] ?? null, function ($q, $from) {
                $q->whereDate('created_at', '>=', $from);
            })
            ->when($filters['to'] ?? null, function ($q, $to) {
                $q->whereDate('created_at', '<=', $to);
            })

            // =========================
            // STATUS FILTER
            // =========================
            ->when($filters['status'] ?? null, function ($q, $status) {
                $q->where('status', $status);
            })

            // =========================
            // DELIVERED BY SEARCH
            // =========================
            ->when($filters['delivered_by'] ?? null, function ($q, $search) {
                $q->whereHas('deliveredBy', function ($q) use ($search) {
                    $q->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('user_name', 'like', "%{$search}%");
                });
            })

            // =========================
            // TEAM FILTER
            // =========================
            ->when($filters['team_id'] ?? null, function ($q, $teamId) {
                $q->whereHas('requestedFor', function ($q) use ($teamId) {
                    $q->where('team_id', $teamId)->orWhereHas('subTeam', function ($qu) use ($teamId) {
                        $qu->where('team_id', $teamId);
                    });
                });
            })

            // =========================
            // SUBTEAM FILTER
            // =========================
            ->when($filters['subteam_id'] ?? null, function ($q, $subteamId) {
                $q->whereHas('requestedFor', function ($q) use ($subteamId) {
                    $q->where('subteam_id', $subteamId);
                });
            });

        $data = $query->latest()->get();

        // =========================
        // FORMAT RESPONSE
        // =========================
        return $data->map(function ($item) {

            $user = $item->requestedFor;
            $deliveredBy = $item->deliveredBy;

            return [
                'id' => $item->id,

                'payment_method' => $item->paymentMethod?->name_ar . '-' . $item->paymentMethod?->name_en,

                'requested_for' =>
                trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''))
                    . ' (' . $user->user_name . ')',

                'mobile' => $user->mobile,

                'team' =>  $user->subTeam?->team?->name ?? $user->team?->name,
                'subteam' => $user->subTeam?->name,

                'notes' => $item->notes,

                'delivered_by' => trim(($deliveredBy->first_name ?? '') . ' ' . ($deliveredBy->last_name ?? ''))
                    . ' (' . $deliveredBy->user_name . ')',

                'requested_amount' => $item->requested_amount,
                'approved_amount' => $item->approved_amount,

                'from_vault_balance' => $item->fromVault?->balance,

                'status' => $item->status,

                'created_at' => $item->created_at_formatted,
            ];
        });
    }
}
