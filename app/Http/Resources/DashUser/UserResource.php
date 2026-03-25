<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'user_name' => $this->user_name,
            'birth_date' => $this->birth_date,
            'mobile' => $this->mobile,
            'profile_link' => $this->profile_link,
            'status'        => $this->status,
            'balance'        => $this->balance,

            'created_at' => $this->created_at_formatted,
            'roles'         => $this->whenLoaded("roles", function () {
                return $this->roles->map(function ($role) {
                    return [
                        'id'   => $role->id,
                        'name' => $role->name,
                    ];
                });
            }),
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
