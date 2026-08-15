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

        $managerIds = Team::query()
            ->whereNotNull('manager_id')
            ->pluck('manager_id');

        AppUser::query()
            ->whereIn('id', $managerIds)
            ->get()
            ->each(function (AppUser $manager) {
                $manager->roles()->sync([]);
                $manager->permissions()->sync([]);

                $manager->assignRole('Team Manager');
            });


        $teamLeaderIds = SubTeam::query()
            ->whereNotNull('team_leader_id')
            ->pluck('team_leader_id');

        AppUser::query()
            ->whereIn('id', $teamLeaderIds)
            ->get()
            ->each(function (AppUser $teamLeader) {
                $teamLeader->roles()->sync([]);
                $teamLeader->permissions()->sync([]);

                $teamLeader->assignRole('Team Leader');
            });
    }
}
