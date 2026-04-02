<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Events\SubTeamsSynced;
use App\Models\AppUser;
use App\Models\SubTeam;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class TeamService
{
    public function list($request)
    {
        return Team::with('manager')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $team = Team::create([
                'name' => $data['name'],
                'active' => $data['active'],
                'marketer_percentage' => $data['marketer_percentage'],

                'team_leader_percentage' => $data['team_leader_percentage'],
                'manager_percentage' => $data['manager_percentage'],
                'direct_manager_percentage' => $data['direct_manager_percentage'],

                // 'delivery_man_percentage' => $data['delivery_man_percentage'],
                // 'warehouse_man_percentage' => $data['warehouse_man_percentage'],
                'manager_id' => isset($data['manager_id']) ? $data['manager_id'] : null
            ]);

            foreach ($data['sub_teams'] ?? [] as $sub) {
                $team->subTeams()->create([
                    'name' => $sub['name'],
                    'active' => $sub['active'],
                    'is_direct' => $sub['is_direct'],
                    'team_leader_id' => $sub['team_leader_id'] ?? null,
                ]);
            }

            $team->load(['manager', 'users', 'subTeams']);

            return $team;
        });
    }

    public function update(Team $team, array $data)
    {
        return DB::transaction(function () use ($team, $data) {
            // $oldManagerId = $team->manager_id;

            $team->update([
                'name' => $data['name'],
                'active' => $data['active'],
                'marketer_percentage' => $data['marketer_percentage'],

                'team_leader_percentage' => $data['team_leader_percentage'],
                'manager_percentage' => $data['manager_percentage'],
                'direct_manager_percentage' => $data['direct_manager_percentage'],

                // 'delivery_man_percentage' => $data['delivery_man_percentage'],
                // 'warehouse_man_percentage' => $data['warehouse_man_percentage'],
                'manager_id' => isset($data['manager_id']) ? $data['manager_id'] : null
            ]);


            if (!empty($data['sub_teams_removed'])) {
                $team->subTeams()
                    ->whereIn('id', $data['sub_teams_removed'])
                    ->delete();
            }

            if (!empty($data['sub_teams'])) {

                $now = now();

                $subTeamsPayload = collect($data['sub_teams'])
                    ->map(function ($sub) use ($team, $now) {
                        return [
                            'id' => $sub['id'] ?? null,
                            'team_id' => $team->id,
                            'name' => $sub['name'],
                            'active' => $sub['active'],
                            'is_direct' => $sub['is_direct'],
                            'team_leader_id' => $sub['team_leader_id'] ?? null,
                            'updated_at' => $now,
                            'created_at' => $now,
                        ];
                    })
                    ->toArray();

                SubTeam::upsert(
                    $subTeamsPayload,
                    ['id'],
                    ['name', 'active', 'is_direct', 'team_leader_id', 'updated_at']
                );
                event(new SubTeamsSynced($team, $data['sub_teams']));
            }

            $team->load(['manager', 'users', 'subTeams']);

            return $team;
        });
    }

    public function updateTeamUsers(Team $team, array $data)
    {
        return DB::transaction(function () use ($team, $data) {

            $directSubTeam = $team->subTeams()
                ->where('is_direct', true)
                ->first();

            $users = $data['users'] ?? [];

            $keepIds = array_merge($users, array_filter([$team->manager_id]));

            AppUser::where('subteam_id', $directSubTeam->id)
                ->whereNotIn('id', $keepIds)
                ->update([
                    'team_id' => null,
                    'subteam_id' => null
                ]);

            AppUser::whereIn('id', $users)
                ->update([
                    'team_id' => $team->id,
                    'subteam_id' => $directSubTeam?->id
                ]);

            $team->load(['manager', 'users']);

            return $team;
        });
    }

    public function show(Team $team)
    {
        $team->load(['manager', 'users', 'subTeams.teamLeader']);
        return $team;
    }

    public function delete(Team $team)
    {
        if ($team->subTeams()->exists() || $team->users()->exists()) {
            return false;
        }

        return $team->delete();
    }


    public function selectAvailable()
    {

        $teams = Team::where('active', true)->orderBy('id')->get([
            'id',
            'name',
            'active',
        ]);

        return $teams;
    }
}
