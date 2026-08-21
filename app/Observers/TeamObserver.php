<?php

namespace App\Observers;

use App\Models\AppUser;
use App\Models\Team;

class TeamObserver
{
    /**
     * Handle the Team "created" event.
     */
    public function created(Team $team): void
    {

        if ($team->manager_id) {

            $manager = AppUser::findOrFail($team->manager_id);

            if ($manager) {
                $manager->update([
                    'team_id' => $team->id,
                    'subteam_id' => null

                ]);
                $manager->roles()->sync([]);
                $manager->permissions()->sync([]);

                $manager->assignRole('Team Manager');
            }
        }
        // if (isset($team->manager_id)) {
        //     AppUser::where('id', $team->manager_id)
        //         ->update([
        //             'team_id' => $team->id
        //         ]);
        // }
    }

    /**
     * Handle the Team "updated" event.
     */
    public function updated(Team $team): void
    {
        if ($team->wasChanged('manager_id')) {

            $oldManagerId = $team->getOriginal('manager_id');

            if ($oldManagerId) {
                $oldManager = AppUser::findOrFail($oldManagerId);

                if ($oldManager) {
                    $oldManager->removeRole('Team Manager');
                    if ((int) $oldManager->team_id === (int) $team->id) {

                        $directSubTeam = $team->subTeams()
                            ->where('is_direct', true)
                            ->first();

                        // Create Direct subteam if it doesn't exist
                        if (!$directSubTeam) {
                            $directSubTeam = $team->subTeams()->create([
                                'name' => 'Direct ' . $team->name,
                                'active' => true,
                                'is_direct' => true,
                                'team_leader_id' => null,
                            ]);
                        }

                        $oldManager->updateQuietly([
                            'subteam_id' => $directSubTeam->id,
                        ]);
                    }
                }
            }

            if ($team->manager_id) {

                $manager = AppUser::findOrFail($team->manager_id);

                if ($manager) {
                    $manager->updateQuietly([
                        'team_id' => $team->id,
                        'subteam_id' => null
                    ]);
                    $manager->roles()->sync([]);
                    $manager->permissions()->sync([]);

                    $manager->assignRole('Team Manager');
                }
            }
        }
    }

    /**
     * Handle the Team "deleted" event.
     */
    public function deleted(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "restored" event.
     */
    public function restored(Team $team): void
    {
        //
    }

    /**
     * Handle the Team "force deleted" event.
     */
    public function forceDeleted(Team $team): void
    {
        //
    }
}
