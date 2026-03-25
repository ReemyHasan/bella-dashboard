<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageAssigneeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'assignee' => $this->resolveAssignee(),

        ];
    }

    private function resolveAssignee(): ?array
    {
        if ($this->relationLoaded('team') && $this->team) {
            return [
                'type' => 'team',
                'id' => $this->team->id,
                'name' => $this->team->name,
            ];
        }

        if ($this->relationLoaded('subTeam') && $this->subTeam) {
            return [
                'type' => 'sub_team',
                'id' => $this->subTeam->id,
                'name' => $this->subTeam->name,
            ];
        }

        if ($this->relationLoaded('marketer') && $this->marketer) {
            return [
                'type' => 'marketer',
                'id' => $this->marketer->id,
                'name' => $this->marketer->first_name
                    . ' '
                    . $this->marketer->last_name
                    . ' ('
                    . $this->marketer->user_name
                    . ')',
            ];
        }

        return null;
    }
}
