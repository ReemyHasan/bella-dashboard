<?php

namespace App\Observers;

use App\Enums\NotificationType;
use App\Events\NotificationEvent;
use App\Models\AppUser;
use App\Models\SubTeam;
use App\Models\Team;

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
        if ($user->wasChanged('team_id')) {
            $this->syncTeamManager($user);
        }

        if ($user->wasChanged('subteam_id')) {
            $this->syncTeamLeader($user);
        }
    }

    private function syncTeamManager(AppUser $user): void
    {
        /*
         * Remove this user as manager from teams
         * that are different from their current team.
         */
        Team::query()
            ->where('manager_id', $user->id)
            ->when(
                $user->team_id,
                fn($query) => $query->where('id', '!=', $user->team_id)
            )
            ->update([
                'manager_id' => null,
            ]);

        /*
         * If the user has no team, they cannot be a manager.
         */
        if (!$user->team_id) {
            $user->removeRole('Team Manager');

            return;
        }

        /*
         * Check whether they are still manager
         * of their current team.
         */
        $isManager = Team::query()
            ->where('id', $user->team_id)
            ->where('manager_id', $user->id)
            ->exists();

        if (!$isManager) {
            $user->removeRole('Team Manager');
        }
    }

    private function syncTeamLeader(AppUser $user): void
    {
        /*
         * Remove this user as leader from sub-teams
         * that are different from their current sub-team.
         */
        SubTeam::query()
            ->where('team_leader_id', $user->id)
            ->when(
                $user->subteam_id,
                fn($query) => $query->where('id', '!=', $user->subteam_id)
            )
            ->update([
                'team_leader_id' => null,
            ]);

        /*
         * If the user has no sub-team,
         * they cannot be a team leader.
         */
        if (!$user->subteam_id) {
            $user->removeRole('Team Leader');

            return;
        }

        /*
         * Check whether they are still leader
         * of their current sub-team.
         */
        $isLeader = SubTeam::query()
            ->where('id', $user->subteam_id)
            ->where('team_leader_id', $user->id)
            ->exists();

        if (!$isLeader) {
            $user->removeRole('Team Leader');
        }
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
