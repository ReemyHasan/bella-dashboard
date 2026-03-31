<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Tip;
use App\Models\Zone;
use Illuminate\Support\Facades\DB;

class ZoneService
{
    public function list($request)
    {
        return Zone::with('currency', 'tips')->filterBy($request->all())
            ->sortBy($request->get('sort', ['name' => 'asc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $zone = Zone::create([
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'currency_id' => $data['currency_id'],

                'delivery_cost' => $data['delivery_cost'],
            ]);
            if (!empty($data['tips'])) {
                $zone->tips()->createMany($data['tips']);
            }
            $zone->load('tips', 'currency');

            return $zone;
        });
    }

    public function update(Zone $zone, array $data)
    {
        return DB::transaction(function () use ($zone, $data) {
            $zone->update([
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'currency_id' => $data['currency_id'],

                'delivery_cost' => $data['delivery_cost'],
            ]);

            if (isset($data['tips'])) {

                $zone->tips()->delete();
                $zone->tips()->createMany($data['tips']);
            }
            $zone->load('tips', 'currency');

            return $zone;
        });
    }
    public function show(Zone $zone)
    {
        $zone->load('tips', 'currency');
        return $zone;
    }

    public function delete(Zone $zone)
    {
        if ($zone->productPrices()->exists() || $zone->id == 1 || $zone->id == 3) {
            return false;
        }
        return $zone->delete();
    }


    public function selectAvailable()
    {

        $zones = Zone::get([
            'id',
            'name',
            'symbol',
        ]);

        return $zones;
    }

    public function selectAvailableTips($zone = null)
    {

        $tips = Tip::when(!is_null($zone), function ($query) use ($zone) {
            $query->where('zone_id', $zone);
        })->get([
            'id',
            'zone_id',
            'amount'
        ]);

        return $tips;
    }
}
