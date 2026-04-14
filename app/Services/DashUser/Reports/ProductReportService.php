<?php

namespace App\Services\DashUser\Reports;

use App\Models\ProductZonePrice;

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
}
