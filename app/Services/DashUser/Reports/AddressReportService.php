<?php

namespace App\Services\DashUser\Reports;

use App\Models\Address;

class AddressReportService
{
    public function addressesReport(array $filters)
    {
        $warehouseMan = $filters['warehouse_man'] ?? null;

        $addresses = Address::query()
            ->with([
                'region.city.zone.currency',
                'region.warehouse.keeper'
            ])
            ->when(!is_null($warehouseMan), function ($q) use ($warehouseMan) {
                $q->whereHas('region.warehouse', function ($q) use ($warehouseMan) {
                    $q->where('keeper_id', $warehouseMan);
                });
            })
            ->get();

        if ($addresses->isEmpty()) {
            return [];
        }

        return $addresses->map(function ($address) {

            $zone = $address->region?->city?->zone;
            $region = $address->region;
            $warehouse = $region?->warehouse;
            $currency = $zone?->currency;

            return [
                'address_id' => $address?->id,
                'address_name' => $address?->name,

                'currency' => $currency
                    ? $currency->name . '(' . $currency->symbol . ')'
                    : null,

                'region' => $region
                    ? $region->name . '(' . $region->symbol . ')'
                    : null,

                'warehouse' => $warehouse?->name,

                'warehouse_man' => $warehouse && $warehouse->keeper
                    ? trim(
                        $warehouse->keeper->first_name . ' ' .
                            $warehouse->keeper->last_name
                    ) . ' (' . $warehouse->keeper->user_name . ')'
                    : null
            ];
        })->values();
    }
}
