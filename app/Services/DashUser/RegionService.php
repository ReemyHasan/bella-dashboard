<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Address;
use App\Models\Region;
use Illuminate\Support\Facades\DB;

class RegionService
{
    public function list($request)
    {
        return Region::with('city', 'warehouse')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $region = Region::create([
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'warehouse_id' => $data['warehouse_id'],
                'delivery_cost' => $data['delivery_cost'],

                'city_id' => $data['city_id']
            ]);
            foreach ($data['addresses'] ?? [] as $sub) {
                $region->addresses()->create([
                    'name' => $sub['name']
                ]);
            }

            $region->load('city', 'warehouse');

            return $region;
        });
    }

    public function update(Region $region, array $data)
    {
        return DB::transaction(function () use ($region, $data) {
            $region->update([
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'warehouse_id' => $data['warehouse_id'],
                'delivery_cost' => $data['delivery_cost'],

                'city_id' => $data['city_id']
            ]);
            if (!empty($data['addresses_removed'])) {
                $region->addresses()
                    ->whereIn('id', $data['addresses_removed'])
                    ->delete();
            }

            if (!empty($data['addresses'])) {

                $now = now();

                $addressesPayload = collect($data['addresses'])
                    ->map(function ($sub) use ($region, $now) {
                        return [
                            'id' => $sub['id'] ?? null,
                            'region_id' => $region->id,
                            'name' => $sub['name'],
                            'updated_at' => $now,
                            'created_at' => $now,
                        ];
                    })
                    ->toArray();

                Address::upsert(
                    $addressesPayload,
                    ['id'],
                    ['name', 'updated_at']
                );
            }
            $region->load('city', 'warehouse');

            return $region;
        });
    }
    public function show(Region $region)
    {
        $region->load('city', 'warehouse');
        return $region;
    }

    public function delete(Region $region)
    {
        return $region->delete();
    }


    public function selectAvailable($city = null, $warehouse = null)
    {

        $regions = Region::when(!is_null($city), function ($query) use ($city) {
            $query->where('city_id', $city);
        })->when(!is_null($warehouse), function ($query) use ($warehouse) {
            $query->where('warehouse_id', $warehouse);
        })->orderBy('id')->get([
            'id',
            'name',
            'symbol',
            'warehouse_id',
            'city_id'
        ]);

        return $regions;
    }
}
