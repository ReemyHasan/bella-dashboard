<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Currency;
use Illuminate\Support\Facades\DB;

class CurrencyService
{
    public function list($request)
    {
        return Currency::filterBy($request->all())
            ->sortBy($request->get('sort', ['name' => 'asc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $currency = Currency::create([
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'is_main' => $data['is_main'],
                'exchange_value' => $data['exchange_value'],
            ]);
            return $currency;
        });
    }

    public function update(Currency $currency, array $data)
    {
        return DB::transaction(function () use ($currency, $data) {
            $currency->update([
                'name' => $data['name'],
                'symbol' => $data['symbol'],
                'is_main' => $data['is_main'],
                'exchange_value' => $data['exchange_value'],

            ]);

            return $currency;
        });
    }
    public function show(Currency $currency)
    {
        $currency->load('zones');
        return $currency;
    }

    public function delete(Currency $currency)
    {
        return $currency->delete();
    }


    public function selectAvailable()
    {

        $currencies = Currency::get([
            'id',
            'name',
            'symbol',
        ]);

        return $currencies;
    }
}
