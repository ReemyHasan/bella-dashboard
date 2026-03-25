<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubTeamResource extends JsonResource
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
            'name' => $this->name,
            'active' => $this->active,
            'is_direct' => $this->is_direct,
            'team_leader' => $this->whenLoaded('teamLeader', fn() => [
                'id' => $this->teamLeader?->id,
                'name' => $this->teamLeader?->first_name . ' ' . $this->teamLeader?->last_name . ' (' . $this->teamLeader?->user_name . ')',
            ]),
            'team' => $this->whenLoaded('team', fn() => [
                'id' => $this->team?->id,
                'name' => $this->team?->name
            ]),
            'created_at' => $this->created_at_formatted,


            'users'         => $this->whenLoaded("users", function () {
                return $this->users->map(function ($user) {
                    return [
                        'id'   => $user->id,
                        'name' => $user?->first_name . ' ' . $user?->last_name . ' (' . $user?->user_name . ')',
                    ];
                });
            }),

        ];
    }
}
