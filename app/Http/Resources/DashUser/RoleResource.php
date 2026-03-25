<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
    return [
            'id'          => $this->id,
            'name'        => $this->name,
            'name_ar'        => $this->name_ar,
            'guard_name'        => $this->guard_name,
            'is_protected' => $this->is_protected ?? false,
            'permissions' => $this->whenLoaded("permissions", function () {
                return $this->permissions
                    ->groupBy('group')
                    ->map(function ($permissions, $groupName) {
                        return [
                            'group' => $groupName,
                            'items' => $permissions->map(function ($permission) {
                                return [
                                    'id'    => $permission->id,
                                    'name'  => $permission->name,
                                    'name_ar' => $permission->name_ar,
                                ];
                            })->values()
                        ];
                    })->values();
            })
        ];
    }
}
