<?php

namespace App\Services\DashUser;

use App\Enums\PaginationEnum;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RoleService
{
    public function assignPermissions(Role $role, array $permissionIds): void
    {
        $role->permissions()->sync($permissionIds);
        $this->refreshPermissionsCache($role);
    }

    public function refreshPermissionsCache(Role $role): void
    {
        Cache::put("role_permissions_{$role->id}", $role->permissions()->pluck('name')->toArray(), now()->addDay());
    }

    public function list($request)
    {
        return Role::filterBy($request->all())
            ->sortBy($request->get('sort', ['name' => 'asc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $role = Role::create([
                'name' => $data['name'],
                'name_ar' => $data['name_ar'],
                'guard_name' => $data['guard_name'],
            ]);

            $this->assignPermissions($role, $data['permissions']);

            return $role;
        });
    }

    public function update(Role $role, array $data)
    {
        return DB::transaction(function () use ($role, $data) {
            $role->update([
                'name' => $data['name'],
                'name_ar' => $data['name_ar'],
            ]);

            $this->assignPermissions($role, $data['permissions']);

            return $role;
        });
    }
    public function show(Role $role)
    {

        $role->load('permissions');
        return $role;
    }

    public function delete(Role $role)
    {
        if ($role->is_protected) {
            return false;
        }

        return $role->delete();
    }

    public function availablePermissions(string $type)
    {

        $permissions = Permission::where('guard_name', $type)
            ->get()
            ->groupBy('group')
            ->map(function ($grouped) {
                return $grouped->map(function ($perm) {
                    return [
                        'id' => $perm->id,
                        'name' => $perm->name,
                        'name_ar' => $perm->name_ar,
                    ];
                });
            });

        return $permissions;
    }


    public function availableRolesByType(string $type)
    {

        $roles = Role::where('guard_name', $type)
            ->get();

        return $roles;
    }
}
