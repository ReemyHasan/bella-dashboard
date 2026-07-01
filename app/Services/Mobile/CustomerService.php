<?php

namespace App\Services\Mobile;

use App\Enums\PaginationEnum;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function list($request)
    {

        $user = auth()->user();

        $query = Customer::with(['createdBy', 'updatedBy'])
            ->where(function ($q) use ($user) {

                // ✅ Created by user
                $q->where(function ($sub) use ($user) {
                    $sub->where('created_by_type', AppUser::class)
                        ->where('created_by_id', $user->id);
                })

                    // ✅ OR has orders created by user
                    ->orWhereHas('orders', function ($sub) use ($user) {
                        $sub->where('app_user_id', $user->id);
                    });
            });

        return $query
            ->filterBy($request->all())
            ->where('is_blocked', false)
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);
    }


    public function create(array $data): Customer
    {
        $user = auth()->user();
        // if (!$user->hasRole('Team Manager') && !$user->hasRole('Team Leader')) {
        //     throw new CustomException('لا يمكن إضافة زبون جديد إلا من قبل مدير أو مدير فريق');
        // }
        return DB::transaction(function () use ($data) {
            $customer = Customer::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'mobile' => $data['mobile'],
                'created_by_id' => auth()->user()->id,
                'created_by_type' => AppUser::class,
            ]);
            $attachData = [];
            foreach ($data['addresses'] as $address) {
                $attachData[$address['address_id']] = [
                    'is_main' => $address['is_main'],
                    'extra_details' => $address['extra_details'],

                ];
            }
            $customer->addresses()->attach($attachData);
            $customer->load(['addresses', 'createdBy', 'updatedBy']);
            return $customer;
        });
    }

    public function update(Customer $customer, array $data): Customer
    {
        $user = auth()->user();
        // if (!$user->hasRole('Team Manager') && !$user->hasRole('Team Leader')) {
        //     throw new CustomException('لا يمكن إضافة زبون جديد إلا من قبل مدير أو مدير فريق');
        // }
        if ($customer->created_by_type != AppUser::class || $customer->created_by_id != $user->id) {
            throw new CustomException('لا يمكن تعديل معلومات زبون إلا من المنشئ له.');
        }
        if ($customer->is_blocked) {
            throw new CustomException('لا يمكن تعديل معلومات زبون, إنه محظور.');
        }
        $customer->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'mobile' => $data['mobile'],
            'updated_by_id' => auth()->user()->id,
            'updated_by_type' => AppUser::class,
        ]);

        $attachData = [];

        foreach ($data['addresses'] as $address) {
            $attachData[$address['address_id']] = [
                'is_main' => $address['is_main'],
                'extra_details' => $address['extra_details'],

            ];
        }

        $customer->addresses()->sync($attachData);
        $customer->load(['addresses', 'createdBy', 'updatedBy']);

        return $customer;
    }

    public function show($id): Customer
    {
        $user = auth()->user();

        return Customer::with(['addresses', 'createdBy', 'updatedBy', 'blockedBy'])
            ->where(function ($q) use ($user) {

                $q->where(function ($sub) use ($user) {
                    $sub->where('created_by_type', AppUser::class)
                        ->where('created_by_id', $user->id);
                })
                    ->orWhereHas('orders', function ($sub) use ($user) {
                        $sub->where('app_user_id', $user->id);
                    });
            })
            ->findOrFail($id);
    }
}
