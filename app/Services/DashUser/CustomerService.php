<?php

namespace App\Services\DashUser;

use App\Enums\DashUserStatus;
use App\Enums\PaginationEnum;
use App\Models\Customer;
use App\Models\DashUser;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function list($request, $trashed = false)
    {

        $query = Customer::with(['createdBy', 'updatedBy', 'blockedBy']);

        if ($trashed) {
            $query->onlyTrashed();
        }
        return $query
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);
    }


    public function create(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $customer = Customer::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                //'user_name' => $data['user_name'],
                'mobile' => $data['mobile'],
                //'password' => $data['password'],
                //'profile_link' => $data['profile_link'],
                'created_by_id' => auth()->user()->id,
                'created_by_type' => DashUser::class,
            ]);



            $attachData = [];

            foreach ($data['addresses'] as $address) {
                $attachData[$address['address_id']] = [
                    'is_main' => $address['is_main'],
                    'extra_details' => $address['extra_details'],

                ];
            }

            $customer->addresses()->attach($attachData);
            $customer->load(['addresses', 'createdBy', 'updatedBy', 'blockedBy']);
            return $customer;
        });
    }

    public function update(Customer $customer, array $data): Customer
    {
        $customer->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            //'user_name' => $data['user_name'],
            'mobile' => $data['mobile'],
            //'profile_link' => $data['profile_link'],
            'updated_by_id' => auth()->user()->id,
            'updated_by_type' => DashUser::class,
        ]);

        $attachData = [];

        foreach ($data['addresses'] as $address) {
            $attachData[$address['address_id']] = [
                'is_main' => $address['is_main'],
                'extra_details' => $address['extra_details'],

            ];
        }

        $customer->addresses()->sync($attachData);
        $customer->load(['addresses', 'createdBy', 'updatedBy', 'blockedBy']);

        return $customer;
    }


    public function updatePassword(Customer $customer, string $password)
    {
        $customer->update([
            'password' => bcrypt($password)
        ]);
    }

    public function delete(Customer $customer)
    {
        return DB::transaction(function () use ($customer) {
            return $customer->delete();
        });
    }

    public function show($id): Customer
    {
        return Customer::with(['addresses', 'createdBy', 'updatedBy', 'blockedBy'])->findOrFail($id);
    }


    public function handleStatusChange(Customer $user, string $action, $blocked_reason = null): string
    {
        return match ($action) {
            'ban'       => $this->updateStatus($user, DashUserStatus::BANNED, $blocked_reason, 'messages.banned_successfully'),
            'unban'     => $this->updateStatus($user, DashUserStatus::ACTIVE, null, 'messages.unbanned_successfully'),
        };
    }

    protected function updateStatus(Customer $user, DashUserStatus $status, $blocked_reason = null, string $messageKey): string
    {
        if ($status == DashUserStatus::BANNED)
            $user->update([
                'blocked_by_id' => auth()->user()->id,
                'blocked_by_type' => DashUser::class,
                'is_blocked' => true,
                'blocked_date' => now(),
                'blocked_reason' => $blocked_reason,
            ]);
        else
            $user->update([
                'blocked_by_id' => null,
                'blocked_by_type' => null,
                'is_blocked' => false,
                'blocked_date' => null,
                'blocked_reason' => null,
            ]);
        return __($messageKey, ['item' => __('constants.customer')]);
    }
}
