<?php

namespace App\Http\Resources\DashUser\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionParticipantResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user_id' => $this->user_id,
            'name' => $this->user?->first_name . ' ' . $this->user?->last_name . ' (' . $this->user?->user_name . ')',
            'score' => $this->score,
            'is_winner' => $this->is_winner,

        ];
    }
}
