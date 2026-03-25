<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $this->loadMissing(['roles','permissions']);

        $roles = $this->roles->map(function ($role) {
            return [
                'id'   => $role->id,
                'name' => $role->name,
            ];
        });

        $permissions = $this->permissions
            ->unique('id')
            ->map(function ($permission) {
                return [
                    'id'   => $permission->id,
                    'name' => $permission->name,
                ];
            })
            ->values();

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
            'roles'         => $roles,
            'permissions'   => $permissions,
        ];
    }
}
