<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'manager' => $this->whenLoaded('manager', fn() => [
                'id' => $this->manager?->id,
                'name' => $this->manager?->first_name . ' ' . $this->manager?->last_name . ' (' . $this->manager?->user_name . ')',
            ]),
            'created_at' => $this->created_at_formatted,

            'marketer_percentage' => $this->marketer_percentage,
            'team_leader_percentage' => $this->team_leader_percentage,
            'manager_percentage' => $this->manager_percentage,
            // 'direct_manager_percentage' => $this->direct_manager_percentage,
            // 'delivery_man_percentage' => $this->delivery_man_percentage,
            // 'warehouse_man_percentage' => $this->warehouse_man_percentage,

            'direct_users'         => $this->whenLoaded("users", function () {

                $directSubTeamId = $this->subTeams
                    ->firstWhere('is_direct', true)?->id;

                return $this->users
                    ->where('subteam_id', $directSubTeamId)
                    ->map(function ($user) {
                        return [
                            'id'   => $user->id,
                            'name' => $user?->first_name . ' ' . $user?->last_name . ' (' . $user?->user_name . ')',
                        ];
                    })
                    ->values();
            }),
            'sub_teams' => SubTeamResource::collection(
                $this->whenLoaded('subTeams')
            ),
        ];
    }
}
