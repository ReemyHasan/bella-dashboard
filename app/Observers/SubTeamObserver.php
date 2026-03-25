<?php

namespace App\Observers;

use App\Models\AppUser;
use App\Models\SubTeam;

class SubTeamObserver
{

public function created(SubTeam $subTeam): void
    {

        if ($subTeam->team_leader_id) {

            $manager = AppUser::find($subTeam->team_leader_id);

            if ($manager) {
                $manager->update([
                    'team_id' => $subTeam->team_id, 'subteam_id' => $subTeam->id
                ]);

                $manager->assignRole('Team Leader');
            }
        }
    }

    /**
     * Handle the Team "updated" event.
     */
    public function updated(SubTeam $subTeam): void
    {
        if ($subTeam->wasChanged('team_leader_id')) {

            $oldManagerId = $subTeam->getOriginal('team_leader_id');

            if ($oldManagerId) {
                $oldManager = AppUser::find($oldManagerId);

                if ($oldManager) {
                    $oldManager->removeRole('Team Leader');
                }
            }

            if ($subTeam->team_leader_id) {

                $manager = AppUser::find($subTeam->team_leader_id);

                if ($manager) {
                    $manager->update([
                        'team_id' => $subTeam->team_id, 'subteam_id' => $subTeam->id
                    ]);

                    $manager->assignRole('Team Leader');
                }
            }
        }
    }

    /**
     * Handle the SubTeam "deleted" event.
     */
    public function deleted(SubTeam $subTeam): void
    {
        //
    }

    /**
     * Handle the SubTeam "restored" event.
     */
    public function restored(SubTeam $subTeam): void
    {
        //
    }

    /**
     * Handle the SubTeam "force deleted" event.
     */
    public function forceDeleted(SubTeam $subTeam): void
    {
        //
    }
}
