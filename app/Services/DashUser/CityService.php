<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\City;
use Illuminate\Support\Facades\DB;

class CityService
{
    public function list($request)
    {
        return City::with('zone')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $city = City::create([
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'zone_id' => $data['zone_id']
            ]);
            return $city;
        });
    }

    public function update(City $city, array $data)
    {
        return DB::transaction(function () use ($city, $data) {
            $city->update([
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'zone_id' => $data['zone_id']
            ]);

            return $city;
        });
    }
    public function show(City $city)
    {
        $city->load('zone');
        return $city;
    }

    public function delete(City $city)
    {
        return $city->delete();
    }


    public function selectAvailable($zone = null)
    {

        $cities = City::when(!is_null($zone), function ($query) use ($zone) {
            $query->where('zone_id', $zone);
        })->orderBy('id')->get([
            'id',
            'name',
            'symbol',
            'zone_id'
        ]);

        return $cities;
    }
}
