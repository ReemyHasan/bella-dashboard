<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\AppUser;
use App\Models\SubTeam;
use Illuminate\Support\Facades\DB;

class SubTeamService
{
    public function list($request)
    {
        return SubTeam::with('teamLeader', 'team')->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $subTeam = SubTeam::create([
                'name' => $data['name'],
                'team_id' => $data['team_id'],
                'active' => $data['active'],
                'is_direct' => $data['is_direct'],
                'team_leader_id' => isset($data['team_leader_id']) ? $data['team_leader_id'] : null,

            ]);

            if (!empty($data['users'])) {
                AppUser::whereIn('id', $data['users'])
                    ->update(['subteam_id' => $subTeam->id]);
            }

            $subTeam->load('teamLeader', 'team', 'users');

            return $subTeam;
        });
    }

    public function update($id, array $data)
    {
        $subTeam = SubTeam::findOrFail($id);

        return DB::transaction(function () use ($subTeam, $data) {
            //$oldManagerId = $subTeam->team_leader_id;

            $subTeam->update([
                'name' => $data['name'],
                'team_id' => $data['team_id'],
                'active' => $data['active'],
                'is_direct' => $data['is_direct'],
                'team_leader_id' => isset($data['team_leader_id']) ? $data['team_leader_id'] : null,

            ]);

            if (isset($data['users'])) {

                AppUser::where('subteam_id', $subTeam->id)
                    ->whereNotIn('id', array_merge($data['users'], [$data['team_leader_id']]))
                    ->update(['subteam_id' => null]);

                AppUser::whereIn('id', $data['users'])
                    ->update(['subteam_id' => $subTeam->id]);
            }
            $subTeam->load('teamLeader', 'team', 'users');

            return $subTeam;
        });
    }
    public function show($id)
    {
        $subTeam = SubTeam::with('teamLeader', 'team', 'users')->findOrFail($id);
        return $subTeam;
    }

    public function delete($id)
    {
        $subTeam = SubTeam::findOrFail($id);

        if ($subTeam->users()->exists()) {
            return false;
        }

        return $subTeam->delete();
    }


    public function selectAvailable($team = null)
    {

        $subTeams = SubTeam::when(!is_null($team), function ($query) use ($team) {
            $query->where('team_id', $team);
        })->where('active', true)->orderBy('id')->get([
            'id',
            'name',
            'active',
            'team_id'
        ]);

        return $subTeams;
    }
}
