<?php

namespace App\Http\Resources\Mobile;

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
        $this->loadMissing(['roles']);

        $roles = $this->roles->map(function ($role) {
            return [
                'id'   => $role->id,
                'name' => $role->name,
            ];
        });

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

            'roles'         => $roles,
            'addresses'         => $this->whenLoaded("addresses", function () {
                return $this->addresses->map(function ($address) {
                    return [
                        'id'   => $address->id,
                        'name' => $address?->name,
                        'is_main'  => (bool) $address->pivot->is_main,
                    ];
                });
            }),
            "fcm_token" => $this->fcm_token
        ];
    }
    private function resolveTeam(): ?array
    {
        if ($this->relationLoaded('subTeam') && $this->subTeam) {
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
