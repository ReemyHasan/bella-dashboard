<?php

namespace App\Services\Shared;

use App\Exceptions\CustomException;
use App\Models\Address;
use App\Models\AppUser;
use App\Models\Customer;
use App\Models\DashUser;
use App\Models\OfferWarehouse;
use App\Models\OfferZonePrice;
use App\Models\ProductWarehouse;
use App\Models\ProductZonePrice;

class SharedInfoService
{

    public function selectMarketerInfo($marketeId = null)
    {

        $user = auth()->user();
        if (get_class($user) != AppUser::class) {
            $user = AppUser::with('team', 'subTeam.team')->find($marketeId);
        }else{
            $user->load('team', 'subTeam.team');
        }

        $team = $user->subTeam
            ? $user->subTeam->team
            : $user->team;

        if (!$team) {
            throw new CustomException('المسوق لا ينتمي إلى فريق');
        }

        if ($user->subTeam) {
            $teamArr = [
                'subTeam' =>  [
                    'id' => $user->subTeam?->id,
                    'name' => $user->subTeam?->name
                ],
                'team' =>  [
                    'id' => $user->subTeam?->team?->id,
                    'name' => $user->subTeam?->team?->name,
                ]
            ];
        } else if ($user->team) {
            $teamArr = [
                'team' =>  [
                    'id' => $user->team?->id,
                    'name' => $user->team?->name
                ],
                'subTeam' =>  null
            ];
        } else {
            $teamArr = [
                'team' =>  null,
                'subTeam' =>  null

            ];
        }

        // $isDirectTeam = $user->subTeam?->is_direct;

        $requiredData = [

            'marketer_percentage' => $team->marketer_percentage,
            'teamleader_percentage' => $team->team_leader_percentage,
            // 'manager_percentage' => $isDirectTeam ? null : $team->manager_percentage
            'manager_percentage' => $team->manager_percentage

        ];

        return array_merge($requiredData, $teamArr);
    }
    public function selectAddressInfo($addressId)
    {
        $address = Address::with('region.city.zone', 'region.warehouse.keeper')->find($addressId);

        if (!$address) {
            throw new CustomException('العنوان غير موجود');
        }

        $zone = $address->region->city->zone;
        $region = $address->region;
        $warehouse = $address->region->warehouse;

        $user = auth()->user();
        if ($user->hasRole('Team Manager') || $user->hasRole('Team Leader') || get_class($user) == DashUser::class) {

            $warehouseKeeper =  [
                'id' => $warehouse?->keeper_id,
                'name' => $warehouse->keeper?->first_name . ' ' . $warehouse->keeper?->last_name . ' (' . $warehouse->keeper?->mobile . ')',
            ];
        } else {
            $warehouseKeeper =  [
                'id' => $warehouse?->keeper_id,
                'name' => $warehouse->keeper?->first_name . ' ' . $warehouse->keeper?->last_name
            ];
        }


        return [
            'delivery_cost' => $region->delivery_cost,
            'currency_id' => $zone->currency_id,
            'currency_name' => $zone->currency?->name,
            'currency_symbol' => $zone->currency?->symbol,
            'current_exchange_rate' => $zone->currency?->exchange_value,
            'zone_id' => $zone->id,
            'zone_name' => $zone->name . '(' . $zone->symbol . ')',
            'region_id' => $region->id,
            'region_name' => $region->name . '(' . $region->symbol . ')',
            'warehouse_id' => $warehouse?->id,
            'warehouse_name' => $warehouse?->name,

            'warehouse_keeper' => $warehouseKeeper
        ];
    }

    public function selectZoneProducts($zoneId)
    {
        $productZonePrices = ProductZonePrice::with('product:id,name')->where('zone_id', $zoneId)
            ->where('is_available', true)
            ->get();

        return $productZonePrices->map(fn($p) => [
            'product_id' => $p->product?->id,
            'product_name' => $p->product?->name,
            'product_price' => $p->price,
            'product_price_after_adjustment' => $p->price_after_adjustment,
        ]);
    }

    public function selectZoneOffers($zoneId)
    {
        $offerZonePrices = OfferZonePrice::with('offer:id,name')->where('zone_id', $zoneId)
            ->where('is_available', true)
            ->get();

        return $offerZonePrices->map(fn($p) => [
            'offer_id' => $p->offer?->id,
            'offer_name' => $p->offer?->name,
            'offer_price' => $p->price,
        ]);
    }
    public function warehouseProducts($warehouseId)
    {
        $products = ProductWarehouse::with(['product:id,name'])->where('warehouse_id', $warehouseId)->get();
        return $products->map(fn($p) => [
            'id' => $p?->product?->id,
            'name' => $p?->product?->name,
            'available' => $p->quantity - $p->reserved_quantity,
            'quantity' => $p->quantity,
            'reserved_quantity' => $p->reserved_quantity
        ]);
    }
    public function warehouseOffers($warehouseId)
    {
        $offers = OfferWarehouse::with(['offer:id,name'])->where('warehouse_id', $warehouseId)->get();

        return $offers->map(fn($p) => [
            'id' => $p?->offer?->id,
            'name' => $p?->offer?->name,
            // 'available' => $p->quantity
        ]);
    }
    public function customerAddresses($customerId)
    {

        $customer = Customer::find($customerId);

        return $customer->addresses->map(fn($address) =>
        [
            'id' => $address->id,
            'address' => $address->full_address ?? $address->name ?? '',
            'extra_details' => $address->pivot->extra_details,
            'is_main' => (bool)$address->pivot->is_main,
        ]);
    }
}
