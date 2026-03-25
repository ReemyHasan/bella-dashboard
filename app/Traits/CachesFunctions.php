<?php

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait CachesFunctions
{
    public function getCachedPermissionsForRole($role)
    {
        return Cache::rememberForever("role_permissions_{$role->id}", function () use ($role) {
            return $role->permissions->pluck('name')->toArray();
        });
    }

    public function clearCachedPermissionsForRole($role)
    {
        Cache::forget("role_permissions_{$role->id}");
        // Cache::forget("role_ancestor_sequence_{$this->id}");

        // foreach ($this->children as $child) {
        //     $child->clearCachedAncestorSequence();
        // }
    }

    public function clearAllRolePermissionCaches()
    {
        foreach ($this->roles as $role) {
            $this->clearCachedPermissionsForRole($role);
        }
    }
}
