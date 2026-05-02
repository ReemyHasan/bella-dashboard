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
        $participant = $this->participant;

        return [
            'participant_type' => $this->resolveType($participant),
            'participant_id' => $this->participant_id,

            'name' => $this->resolveName($participant),

            'score' => $this->score,
            'progress' => $this->progress,
            'is_winner' => $this->is_winner,
        ];
    }
    private function resolveType($participant): ?string
    {
        return match (true) {
            $participant instanceof \App\Models\AppUser => 'مسوق',
            $participant instanceof \App\Models\Team => 'فريق رئيسي',
            $participant instanceof \App\Models\SubTeam => 'فريق فرعي',
            default => null,
        };
    }
    private function resolveName($participant): ?string
    {
        if (!$participant) {
            return null;
        }

        return match (true) {

            // 🔹 User
            $participant instanceof \App\Models\AppUser =>
            trim($participant->first_name . ' ' . $participant->last_name)
                . ' (' . $participant->user_name . ')',

            // 🔹 Team
            $participant instanceof \App\Models\Team =>
            $participant->name,

            // 🔹 SubTeam
            $participant instanceof \App\Models\SubTeam =>
            $participant->name,

            default => null,
        };
    }
}
