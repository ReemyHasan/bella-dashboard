<?php

namespace Database\Seeders;

use App\Models\AppUser;
use App\Models\SubTeam;
use App\Models\Team;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TeamManagerRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Team::query()
            ->whereNotNull('manager_id')
            ->get()
            ->each(function (Team $team) {

                $manager = AppUser::find($team->manager_id);

                if (!$manager) {
                    return;
                }

                $manager->roles()->sync([]);
                $manager->permissions()->sync([]);

                $manager->update([
                    'team_id' => $team->id,
                    'subteam_id' => null,
                ]);

                $manager->assignRole('Team Manager');
            });

        SubTeam::query()
            ->whereNotNull('team_leader_id')
            ->with('team')
            ->get()
            ->each(function (SubTeam $subTeam) {

                $teamLeader = AppUser::find($subTeam->team_leader_id);

                if (!$teamLeader) {
                    return;
                }

                // $isManager = Team::query()
                //     ->where('manager_id', $teamLeader->id)
                //     ->exists();

                // if ($isManager) {
                //     return;
                // }

                $teamLeader->roles()->sync([]);
                $teamLeader->permissions()->sync([]);


                $teamLeader->update([
                    'team_id' => $subTeam->team_id,
                    'subteam_id' => $subTeam->id,
                ]);

                /*
                 * Assign only Team Leader.
                 */
                $teamLeader->assignRole('Team Leader');
            });


    }
}
