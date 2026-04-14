<?php

namespace App\Services\DashUser\Reports;

use App\Models\ProductWarehouse;

class WarehouseReportService
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


}
