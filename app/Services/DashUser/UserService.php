<?php

namespace App\Services\DashUser;

use App\Enums\DashUserStatus;
use App\Enums\PaginationEnum;
use App\Models\DashUser;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UserService
{
    public function list($request, $trashed = false)
    {

        $query = DashUser::with(['roles']);

        if ($trashed) {
            $query->onlyTrashed();
        }
        return $query
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);
    }


    public function create(array $data): DashUser
    {
        return DB::transaction(function () use ($data) {
            $user = DashUser::create([
                'first_name'     => $data['first_name'],
                'last_name'  => $data['last_name'],
                'user_name'   => $data['user_name'],
                'birth_date'         => $data['birth_date'],
                'mobile'    => $data['mobile'],
                'status'        => $data['status'],
                'password'      => $data['password'],
                'profile_link'      => $data['profile_link'],
                'balance' => $data['balance'],

            ]);

            $roleIds = $data['roles'] ?? [];

            $user->roles()->sync($roleIds);

            if (!empty($roleIds)) {
                $this->syncUserPermissionsFromRoles($user, $roleIds);
            }

            $user->load(['roles', 'permissions']);
            return $user;
        });
    }

    public function update(DashUser $user, array $data): DashUser
    {
        $user->update([
            'first_name'     => $data['first_name'],
            'last_name'  => $data['last_name'],
            'user_name'   => $data['user_name'],
            'birth_date'         => $data['birth_date'],
            'mobile'    => $data['mobile'],
            'status'        => $data['status'],
            'profile_link'      => $data['profile_link'],
            'balance' => $data['balance'],

        ]);
        $roleIds = $data['roles'] ?? [];

        $user->roles()->sync($roleIds);

        if (!empty($roleIds)) {
            $this->syncUserPermissionsFromRoles($user, $roleIds);
        }

        $user->load(['roles', 'permissions']);
        return $user;
    }


    private function syncUserPermissionsFromRoles(DashUser $user, array $roleIds): void
    {
        $permissionIds = DB::table('role_has_permissions')
            ->whereIn('role_id', $roleIds)
            ->pluck('permission_id')
            ->unique()
            ->toArray();

        $user->permissions()->sync($permissionIds);
    }
    public function updatePassword(DashUser $user, string $password)
    {
        $user->update([
            'password' => bcrypt($password)
        ]);
    }

    public function delete(DashUser $user): void
    {
        DB::transaction(function () use ($user) {
            $this->extractRole($user);
            $user->delete();
        });
    }

    public function show($id): DashUser
    {
        return DashUser::with(['roles', 'permissions'])->findOrFail($id);
    }


    public function updatePermissions(DashUser $user, $permissionIds): DashUser
    {
        DB::transaction(function () use ($user, $permissionIds) {
            $user->permissions()->sync($permissionIds);
        });

        $user->load(['roles', 'permissions']);

        return $user;
    }

    public function handleStatusChange(DashUser $user, string $action): string
    {
        return match ($action) {
            'ban'       => $this->updateStatus($user, DashUserStatus::BANNED, 'messages.banned_successfully'),
            'unban'     => $this->updateStatus($user, DashUserStatus::ACTIVE, 'messages.unbanned_successfully'),
            'activate'  => $this->updateStatus($user, DashUserStatus::ACTIVE, 'messages.activated_successfully'),
            'deactivate' => $this->updateStatus($user, DashUserStatus::INACTIVE, 'messages.deactivated_successfully'),
        };
    }

    protected function updateStatus(DashUser $user, DashUserStatus $status, string $messageKey): string
    {
        $user->update(['status' => $status->value]);
        return __($messageKey, ['item' => __('constants.dash_user')]);
    }



    public function extractRole(DashUser $user)
    {

        $user->roles()->sync([]);
        $user->permissions()->sync([]);


        return $user;
    }


    public function selectAvailable(
        $role = null
    ) {

        $users = DashUser::query()->with('roles')
            ->when($role, function ($query) use ($role) {
                $query->whereHas('roles', function ($q) use ($role) {
                    $q->where('name', $role);
                });
            })
            ->where('status', DashUserStatus::ACTIVE->value)->orderBy('id')->get([
                'id',
                'first_name',
                'last_name',
                'user_name',
                'status'
            ]);

        return $users;
    }
}
