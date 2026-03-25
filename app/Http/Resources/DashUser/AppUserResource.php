<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppUserResource extends JsonResource
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
            'birth_date' => $this->birth_date_formatted,
            'join_date' => $this->join_date_formatted,
            'mobile' => $this->mobile,
            'profile_link' => $this->profile_link,
            'status'        => $this->status,
            'created_at' => $this->created_at_formatted,

            'balance'        => $this->balance,
            // 'is_delivery_man'        => $this->is_delivery_man,
            'is_warehouse_man'        => $this->is_warehouse_man,

            'team' => $this->resolveTeam(),

            'subTeam' => $this->whenLoaded('subTeam', fn() => [
                'id' => $this->subTeam?->id,
                'name' => $this->subTeam?->name
            ]),

            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse?->id,
                'name' => $this->warehouse?->name,
            ]),

            'created_by_type' => $this->creator_type,

            'creator' => $this->whenLoaded('createdByAppUser', fn() => [
                'id' => $this->createdByAppUser?->id,
                'name' => $this->createdByAppUser?->first_name . ' ' . $this->createdByAppUser?->last_name . ' (' . $this->createdByAppUser?->user_name . ')',
            ]),
            'creator' => $this->whenLoaded('createdByDashUser', fn() => [
                'id' => $this->createdByDashUser?->id,
                'name' => $this->createdByDashUser?->first_name . ' ' . $this->createdByDashUser?->last_name . ' (' . $this->createdByDashUser?->user_name . ')',
            ]),


            'addresses'         => $this->whenLoaded("addresses", function () {
                return $this->addresses->map(function ($address) {
                    return [
                        'id'   => $address->id,
                        'name' => $address?->name,
                        'is_main'  => (bool) $address->pivot->is_main,
                    ];
                });
            }),

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

    private function resolveTeam(): ?array
    {
        if ($this->relationLoaded('subTeam.team') && $this->subTeam) {
            return [
                'id' => $this->subTeam?->team?->id,
                'name' => $this->subTeam?->team?->name,
            ];
        }
        if ($this->relationLoaded('team') && $this->team) {
            return [
                'id' => $this->team?->id,
                'name' => $this->team?->name
            ];
        }





        return null;
    }
}
