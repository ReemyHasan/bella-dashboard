<?php

namespace App\Services\Shared;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionTarget;
use App\Enums\DashUserStatus;
use App\Exceptions\CustomException;
use App\Models\Address;
use App\Models\AppUser;
use App\Models\Competition;
use App\Models\Customer;
use App\Models\DashUser;
use App\Models\OfferWarehouse;
use App\Models\OfferZonePrice;
use App\Models\ProductWarehouse;
use App\Models\ProductZonePrice;
use App\Models\SubTeam;
use App\Models\UserRequestType;
use App\Models\Warehouse;

class SharedInfoService
{
    public function selectCustomers($search = null)
    {
        $customers = Customer::query()->when(!is_null($search), function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('mobile', 'like', "%$search%");
            });
        })->where('is_blocked', false)->get([
            'id',
            'first_name',
            'last_name',
            'mobile',
        ]);
        return $customers->map(fn($customer) => [
            'key'          => $customer->id,
            'value' => $customer->first_name . ' ' . $customer->last_name . "({$customer->mobile})",
        ]);
    }
    public function selectMarketerInfo($marketeId = null)
    {

        $user = auth()->user();
        if (get_class($user) != AppUser::class) {
            $user = AppUser::with('team', 'subTeam.team')->find($marketeId);
        } else {
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
        if ($user->hasRole('Team Manager') || $user->hasRole('Team Leader') || $user->is_warehouse_man || $user instanceof DashUser) {
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

    public function selectAvailableSubteams($team = null)
    {

        $subTeams = SubTeam::with('team:id,name')->when(!is_null($team), function ($query) use ($team) {
            $query->where('team_id', $team);
        })->where('active', true)->orderBy('id')->get([
            'id',
            'name',
            'active',
            'team_id',
            'is_direct'
        ]);

        return $subTeams;
    }

    public function selectAvailableCompetitions($marketerId = null, $status = CompetitionStatus::active->value)
    {

        $user = auth()->user();
        if (get_class($user) != AppUser::class) {
            $marketer = $marketerId == null ? null : AppUser::findOrFail($marketerId);
        } else {
            $marketer = $user;
            $status = CompetitionStatus::active->value;
        }

        return Competition::query()
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($marketer), function ($query) use ($marketer) {

                $query->where(function ($query) use ($marketer) {

                    // 🔹 Case: ALL → everyone participates
                    $query->where('target', CompetitionTarget::all->value);

                    // 🔹 Case: marketers → directly assigned
                    $query->orWhere(function ($q) use ($marketer) {
                        $q->where('target', CompetitionTarget::marketers->value)
                            ->whereHas('marketers', function ($q2) use ($marketer) {
                                $q2->where('marketer_id', $marketer->id);
                            });
                    });

                    // 🔹 Case: teams → marketer belongs to team
                    $query->orWhere(function ($q) use ($marketer) {
                        $q->where('target', CompetitionTarget::teams->value)
                            ->whereHas('teams', function ($q2) use ($marketer) {
                                $q2->where('team_id', $marketer->team_id);
                            });
                    });

                    // 🔹 Case: subteams → marketer belongs to subteam
                    $query->orWhere(function ($q) use ($marketer) {
                        $q->where('target', CompetitionTarget::subteams->value)
                            ->whereHas('subteams', function ($q2) use ($marketer) {
                                $q2->where('sub_team_id', $marketer->sub_team_id);
                            });
                    });
                });
            })

            ->orderBy('id')
            ->get([
                'id',
                'name',
                'status'
            ]);
    }

    public function selectAvailableAppUserRequestTypes()
    {

        $userRequestTypes = UserRequestType::orderBy('id')->get([
            'id',
            'name'
        ]);

        return $userRequestTypes->map(fn($userRequestType) => [
            'key' => $userRequestType?->id,
            'value' => $userRequestType?->name
        ]);
    }


    public function selectAvailableWarehouseMen($search = null)
    {
        $warehousemen = AppUser::query()->where('is_warehouse_man', true)->when(!is_null($search), function ($query) use ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%$search%")
                    ->orWhere('last_name', 'like', "%$search%")
                    ->orWhere('user_name', 'like', "%$search%");
            });
        })->where('status', DashUserStatus::ACTIVE->value)->get([
            'id',
            'first_name',
            'last_name',
            'user_name',
        ]);
        return $warehousemen->map(fn($warehouseman) => [
            'key'          => $warehouseman->id,
            'value' => $warehouseman->first_name . ' ' . $warehouseman->last_name,
        ]);
    }

    public function selectAvailableWarehouses($zone = null, $is_main = null)
    {
        $warehouses = Warehouse::when(!is_null($zone), function ($query) use ($zone) {
            $query->where('zone_id', $zone);
        })->when(!is_null($is_main), function ($query) use ($is_main) {
            $query->where('is_main', $is_main);
        })->where('active', true)->orderBy('id')->get([
            'id',
            'name',
            'active',
            'is_main',
            'zone_id'
        ]);

        return $warehouses;
    }
}
