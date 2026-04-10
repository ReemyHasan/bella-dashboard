<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Address;
use App\Models\AppUser;
use Illuminate\Support\Facades\DB;

class AddressService
{
    public function list($request)
    {
        return Address::with(
            // 'deliveryMan', 'alterDeliveryMan', 
            'region'
        )->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $address = Address::create([
                'name' => $data['name'],
                // 'delivery_man_id' => $data['delivery_man_id'],
                // 'alter_delivery_man_id' => $data['alter_delivery_man_id'],
                'region_id' => $data['region_id']
            ]);


            // if (!empty($data['delivery_man_id'])) {

            //     $mainUser = AppUser::findOrFail($data['delivery_man_id']);

            //     if ($mainUser && !$mainUser->addresses()
            //         ->where('address_id', $address->id)
            //         ->exists()) {

            //         $mainUser->update([
            //             'is_delivery_man' => true
            //         ]);

            //         $mainUser->addresses()->attach($address->id, [
            //             'is_main' => true
            //         ]);
            //     }
            // }


            // if (!empty($data['alter_delivery_man_id'])) {

            //     $alterUser = AppUser::findOrFail($data['alter_delivery_man_id']);

            //     if ($alterUser && !$alterUser->addresses()
            //         ->where('address_id', $address->id)
            //         ->exists()) {

            //         $alterUser->update([
            //             'is_delivery_man' => true
            //         ]);

            //         $alterUser->addresses()->attach($address->id, [
            //             'is_main' => false
            //         ]);
            //     }
            // }

            $address->load(
                // 'deliveryMan', 'alterDeliveryMan', 
                'region'
            );

            return $address;
        });
    }

    public function update(Address $address, array $data)
    {
        return DB::transaction(function () use ($address, $data) {
            $address->update([
                'name' => $data['name'],
                // 'delivery_man_id' => $data['delivery_man_id'],
                // 'alter_delivery_man_id' => $data['alter_delivery_man_id'],
                'region_id' => $data['region_id']
            ]);

            // if (!empty($data['delivery_man_id'])) {

            //     $mainUser = AppUser::findOrFail($data['delivery_man_id']);

            //     $mainUser->update([
            //         'is_delivery_man' => true
            //     ]);
            //     $mainUser->addresses()
            //         ->syncWithoutDetaching([
            //             $address->id => ['is_main' => true]
            //         ]);
            // }

            // if (!empty($data['alter_delivery_man_id'])) {

            //     $alterUser = AppUser::findOrFail($data['alter_delivery_man_id']);

            //     $alterUser->update([
            //         'is_delivery_man' => true
            //     ]);

            //     $alterUser->addresses()
            //         ->syncWithoutDetaching([
            //             $address->id => ['is_main' => false]
            //         ]);
            // }
            $address->load(
                // 'deliveryMan', 'alterDeliveryMan', 
                'region'
            );

            return $address;
        });
    }
    public function show(Address $address)
    {
        $address->load(
            // 'deliveryMan', 'alterDeliveryMan',
            'region.city.zone'
        );
        return $address;
    }

    public function delete(Address $address)
    {
        return $address->delete();
    }


    public function selectAvailable($region = null)
    {

        $addresses = Address::when(!is_null($region), function ($query) use ($region) {
            $query->where('region_id', $region);
        })->orderBy('id')->get([
            'id',
            'name',
            'region_id'
        ]);

        return $addresses;
    }

    public function marketerAddresses($marketerId)
    {

        $marketer = AppUser::find($marketerId);

        return $marketer->addresses->map(fn($address) =>
        [
            'id' => $address->id,
            'address' => $address->full_address ?? $address->name ?? '',
            'is_main' => (bool)$address->pivot->is_main,
        ]);
    }
}
