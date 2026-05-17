<?php

namespace App\Observers;

use App\Enums\NotificationType;
use App\Events\NotificationEvent;
use App\Models\AppUser;

class AppUserObserver
{
    /**
     * Handle the AppUser "created" event.
     */
    public function created(AppUser $user): void
    {
        event(new NotificationEvent(
            type: NotificationType::NEW_MARKETER,
            data: [
                'marketer' => $user->load([
                    'team.manager',
                    'subTeam.teamLeader',
                ]),
            ]
        ));
        // DB::transaction(function () use ($user) {

        //     if (!$user->is_warehouse_man) {
        //         return;
        //     }

        //     // Create DashUser
        //     $dashUser = DashUser::create([
        //         'first_name' => $user->first_name,
        //         'last_name'  => $user->last_name,
        //         'user_name'  => $user->user_name,
        //         'mobile'     => $user->mobile,
        //         'password'   => $user->password,
        //         'birth_date' => $user->birth_date,
        //         'profile_link' => $user->profile_link,
        //         'status' => $user->status,
        //         'app_user_id' => $user->id
        //     ]);

        //     // Assign role
        //     $role = Role::where('name', 'Warehouse Keeper')->first();

        //     if ($role) {
        //         $dashUser->roles()->syncWithoutDetaching([$role->id]);
        //     }

        //     if ($user->warehouse_id) {
        //         Warehouse::where('id', $user->warehouse_id)
        //             ->update([
        //                 'keeper_id' => $dashUser->id
        //             ]);
        //     }
        // });
    }

    /**
     * Handle the AppUser "updated" event.
     */
    public function updated(AppUser $user): void
    {
        $user->clearRolesCache();

        if ($user->wasChanged('balance')) {

            event(new NotificationEvent(
                type: NotificationType::FINANCIAL_MOVEMENT,
                data: [
                    'user' => $user,
                    'old_balance' => $user->getOriginal('balance'),
                    'new_balance' => $user->balance,
                    'difference' => $user->balance - $user->getOriginal('balance'),
                ]
            ));
        }
        // DB::transaction(function () use ($user) {

        //     $wasKeeper = $user->getOriginal('is_warehouse_man');
        //     $isKeeper  = $user->is_warehouse_man;

        //     $dashUser = $user->dashUser;

        //     $role = Role::where('name', 'Warehouse Keeper')->first();

        //     /**
        //      * Case 1: false -> true
        //      * User became a warehouse keeper
        //      */
        //     if (!$wasKeeper && $isKeeper) {

        //         if (!$dashUser) {
        //             $dashUser = DashUser::create([
        //                 'first_name' => $user->first_name,
        //                 'last_name'  => $user->last_name,
        //                 'user_name'  => $user->user_name,
        //                 'mobile'     => $user->mobile,
        //                 'password'   => $user->password,
        //                 'birth_date' => $user->birth_date,
        //                 'profile_link' => $user->profile_link,
        //                 'status' => $user->status,
        //                 'app_user_id' => $user->id
        //             ]);
        //         }

        //         if ($role) {
        //             $dashUser->roles()->syncWithoutDetaching([$role->id]);
        //         }

        //         if ($user->warehouse_id) {
        //             Warehouse::where('id', $user->warehouse_id)
        //                 ->update(['keeper_id' => $dashUser->id]);
        //         }
        //     }

        //     /**
        //      * Case 2: true -> false
        //      * User is no longer a warehouse keeper
        //      */
        //     if ($wasKeeper && !$isKeeper) {

        //         if ($dashUser) {

        //             if ($role) {
        //                 $dashUser->roles()->detach($role->id);
        //             }

        //             Warehouse::where('keeper_id', $dashUser->id)
        //                 ->update(['keeper_id' => null]);

        //             // $dashUser->delete();
        //         }
        //     }

        //     /**
        //      * Case 3: true -> true but warehouse changed
        //      */
        //     if ($wasKeeper && $isKeeper && $user->wasChanged('warehouse_id')) {

        //         if ($dashUser) {

        //             Warehouse::where('keeper_id', $dashUser->id)
        //                 ->update(['keeper_id' => null]);

        //             if ($user->warehouse_id) {
        //                 Warehouse::where('id', $user->warehouse_id)
        //                     ->update(['keeper_id' => $dashUser->id]);
        //             }
        //         }
        //     }
        // });
    }

    /**
     * Handle the AppUser "deleted" event.
     */
    public function deleted(AppUser $appUser): void
    {
        //
    }

    /**
     * Handle the AppUser "restored" event.
     */
    public function restored(AppUser $appUser): void
    {
        //
    }

    /**
     * Handle the AppUser "force deleted" event.
     */
    public function forceDeleted(AppUser $appUser): void
    {
        //
    }
}
