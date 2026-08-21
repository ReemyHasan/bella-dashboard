<?php

namespace Database\Seeders;

use App\Models\AppUser;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignUsersToDirectSubTeamsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {

            AppUser::query()
                ->whereNotNull('team_id')
                ->whereNull('subteam_id')
                ->with('team')
                ->chunkById(500, function ($users) {

                    foreach ($users as $user) {

                        $team = $user->team;

                        if (!$team) {
                            continue;
                        }

                        // Team manager should not belong to a subteam
                        if ((int) $team->manager_id == (int) $user->id) {
                            continue;
                        }

                        // Find existing Direct subteam
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

                        // Assign user to Direct subteam
                        $user->updateQuietly([
                            'subteam_id' => $directSubTeam->id,
                        ]);
                    }
                });
        });
    }
}
