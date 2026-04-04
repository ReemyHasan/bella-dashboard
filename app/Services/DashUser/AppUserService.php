<?php

namespace App\Services\DashUser;

use App\Enums\DashUserStatus;
use App\Enums\PaginationEnum;
use App\Models\AppUser;
use App\Models\SubTeam;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class AppUserService
{
    public function list($request, $trashed = false)
    {

        $query = AppUser::with(['roles', 'team', 'subTeam.team']);

        if ($trashed) {
            $query->onlyTrashed();
        }
        return $query
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);
    }


    public function create(array $data): AppUser
    {
        return DB::transaction(function () use ($data) {
            $user = AppUser::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'user_name' => $data['user_name'],
                'mobile' => $data['mobile'],
                'password' => $data['password'],
                'birth_date' => $data['birth_date'],
                'join_date' => $data['join_date'],
                'status' => $data['status'],
                'team_id' => $data['subteam_id'] ? null : $data['team_id'],
                'subteam_id' => $data['subteam_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'balance' => $data['balance'],
                'profile_link' => $data['profile_link'],

                'created_by_dash_user_id' => auth()->user()->id,

                // 'is_delivery_man' => $data['is_delivery_man'],
                // 'is_warehouse_man' => $data['is_warehouse_man'],
            ]);


            // $roleIds = $data['roles'] ?? [];

            // $user->roles()->sync($roleIds);

            // if (!empty($roleIds)) {
            //     $this->syncUserPermissionsFromRoles($user, $roleIds);
            // }

            $attachData = [];

            if (isset($data['addresses'])) {
                foreach ($data['addresses'] as $address) {
                    $attachData[$address['id']] = [
                        'is_main' => $address['is_main']
                    ];
                }

                $user->addresses()->attach($attachData);
            }

            $user->load(['roles', 'permissions', 'addresses', 'team', 'subTeam.team']);
            return $user;
        });
    }

    public function update(AppUser $user, array $data): AppUser
    {
        $user->update([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'user_name' => $data['user_name'],
            'mobile' => $data['mobile'],
            'birth_date' => $data['birth_date'],
            'join_date' => $data['join_date'],
            'status' => $data['status'],
            'team_id' => $data['subteam_id'] ? null : $data['team_id'],
            'subteam_id' => $data['subteam_id'],
            'warehouse_id' => $data['warehouse_id'] ?? null,
            'balance' => $data['balance'],
            'profile_link' => $data['profile_link'],

            // 'is_delivery_man' => $data['is_delivery_man'],
            // 'is_warehouse_man' => $data['is_warehouse_man'],
        ]);
        // $roleIds = $data['roles'] ?? [];

        // $user->roles()->sync($roleIds);

        // if (!empty($roleIds)) {
        //     $this->syncUserPermissionsFromRoles($user, $roleIds);
        // }


        $attachData = [];

        if (isset($data['addresses'])) {
            foreach ($data['addresses'] as $address) {
                $attachData[$address['id']] = [
                    'is_main' => $address['is_main']
                ];
            }

            $user->addresses()->sync($attachData);
        }

        $user->load(['roles', 'permissions', 'addresses', 'team', 'subTeam.team']);

        return $user;
    }


    private function syncUserPermissionsFromRoles(AppUser $user, array $roleIds): void
    {
        $permissionIds = DB::table('role_has_permissions')
            ->whereIn('role_id', $roleIds)
            ->pluck('permission_id')
            ->unique()
            ->toArray();

        $user->permissions()->sync($permissionIds);
    }
    public function updatePassword(AppUser $user, string $password)
    {
        $user->update([
            'password' => bcrypt($password)
        ]);
    }

    public function delete(AppUser $user)
    {
        return DB::transaction(function () use ($user) {
            $this->extractRole($user);
            return $user->delete();
        });
    }

    public function show($id): AppUser
    {
        return AppUser::with(['roles', 'permissions', 'addresses', 'createdByAppUser', 'createdByDashUser', 'team', 'subTeam.team', 'warehouse'])->findOrFail($id);
    }


    public function updatePermissions(AppUser $user, $permissionIds): AppUser
    {
        DB::transaction(function () use ($user, $permissionIds) {
            $user->permissions()->sync($permissionIds);
        });

        $user->load(['roles', 'permissions']);

        return $user;
    }

    public function handleStatusChange(AppUser $user, string $action): string
    {
        return match ($action) {
            'ban'       => $this->updateStatus($user, DashUserStatus::BANNED, 'messages.banned_successfully'),
            'unban'     => $this->updateStatus($user, DashUserStatus::ACTIVE, 'messages.unbanned_successfully'),
            'activate'  => $this->updateStatus($user, DashUserStatus::ACTIVE, 'messages.activated_successfully'),
            'deactivate' => $this->updateStatus($user, DashUserStatus::INACTIVE, 'messages.deactivated_successfully'),
        };
    }

    protected function updateStatus(AppUser $user, DashUserStatus $status, string $messageKey): string
    {
        $user->update(['status' => $status->value]);
        return __($messageKey, ['item' => __('constants.app_user')]);
    }



    public function extractRole(AppUser $user)
    {

        $user->roles()->sync([]);
        $user->permissions()->sync([]);


        return $user;
    }

    public function selectAvailable(
        $team = null,
        $subTeam = null,
        $onlyUnassignedTeam = null,
        $isWarehouseMan = null,
        $isTeamManager = null,
        $isSubTeamLeader = null
    ) {

        $users = AppUser::query()

            ->when($isTeamManager, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', 'Team Manager');
                });
            })
            ->when($isSubTeamLeader, function ($query) {
                $query->whereHas('roles', function ($q) {
                    $q->where('name', 'Team Leader');
                });
            })
            ->when($onlyUnassignedTeam, function ($query) {
                $query->whereNull('team_id')->whereNull('subteam_id');
            })
            ->when(!is_null($team), function ($query) use ($team) {
                $query->where('team_id', $team);
            })->when(!is_null($subTeam), function ($query) use ($subTeam) {
                $query->where('subteam_id', $subTeam);
            })->when(!is_null($isWarehouseMan), function ($query) use ($isWarehouseMan) {
                $query->where('is_warehouse_man', $isWarehouseMan);
            })
            ->where('status', DashUserStatus::ACTIVE->value)->orderBy('id')->get([
                'id',
                'subteam_id',
                'team_id',
                'first_name',
                'last_name',
                'user_name',
                'status',
                'is_delivery_man',
                'is_warehouse_man',
            ]);

        return $users;
    }
}
