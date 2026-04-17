<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\AppUser;
use App\Models\SubTeam;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

            // if (!empty($data['users'])) {
            //     AppUser::whereIn('id', $data['users'])
            //         ->update(['subteam_id' => $subTeam->id]);
            // }

            if (!empty($data['users'])) {

                foreach ($data['users'] as $userData) {

                    if (is_numeric($userData)) {
                        AppUser::where('id', $userData)
                            ->update(['subteam_id' => $subTeam->id]);

                        continue;
                    }

                    $user = AppUser::create([
                        'subteam_id' => $subTeam->id,
                        'first_name' => $userData['first_name'],
                        'last_name' => $userData['last_name'] ?? null,
                        'user_name' => $userData['user_name'] ?? null,
                        'mobile' => $userData['mobile'],
                        'password' => bcrypt($userData['password'] ?? '123456'),
                        'created_by_dash_user_id' => auth()->id(),
                    ]);
                    $user->addresses()->sync([
                        $userData['address'] => ['is_main' => true]
                    ]);
                }
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
            if (array_key_exists('users', $data)) {

                $userIds = collect($data['users'] ?? [])
                    ->filter(fn($u) => is_numeric($u))
                    ->map(fn($id) => (int) $id)
                    ->values();

                $keepIds = $userIds->when(
                    !empty($data['team_leader_id']),
                    fn($c) => $c->push((int) $data['team_leader_id'])
                )->unique()->values();

                // Remove users not in payload
                AppUser::where('subteam_id', $subTeam->id)
                    ->when($keepIds->isNotEmpty(), fn($q) => $q->whereNotIn('id', $keepIds))
                    ->when($keepIds->isEmpty(), fn($q) => $q->whereNotNull('id'))
                    ->update(['subteam_id' => null]);

                // Assign existing users
                AppUser::whereIn('id', $userIds)
                    ->update(['subteam_id' => $subTeam->id]);

                // Create new users
                foreach ($data['users'] as $userData) {

                    if (is_array($userData)) {

                        $user = AppUser::create([
                            'subteam_id' => $subTeam->id,
                            'first_name' => $userData['first_name'],
                            'last_name' => $userData['last_name'] ?? null,
                            'user_name' => $userData['user_name'] ?? null,

                            'mobile' => $userData['mobile'],
                            'password' => bcrypt($userData['password'] ?? '123456'),
                            'created_by_dash_user_id' => auth()->id(),
                        ]);

                        $user->addresses()->sync([
                            $userData['address'] => ['is_main' => true]
                        ]);
                    }
                }
            }
            // if (isset($data['users'])) {

            //     AppUser::where('subteam_id', $subTeam->id)
            //         ->whereNotIn('id', array_merge($data['users'], [$data['team_leader_id']]))
            //         ->update(['subteam_id' => null]);

            //     AppUser::whereIn('id', $data['users'])
            //         ->update(['subteam_id' => $subTeam->id]);
            // }
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

        $subTeams = SubTeam::with('team:id,name')->when(!is_null($team), function ($query) use ($team) {
            $query->where('team_id', $team);
        })->where('active', true)->orderBy('id')->get([
            'id',
            'name',
            'active',
            'team_id',
            'is_direct'
        ]);

        return $subTeams;
    }
}
