<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\DashUser\MessageAssigneeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MessageResource extends JsonResource
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
            'description' => $this->description,

            'appears_from' => $this->appears_from_formatted,
            'appears_to' => $this->appears_to_formatted,
            'created_at' => $this->created_at_formatted,

            'assignment_type' => $this->assignment_type->value,
            'target_type' => $this->target_type->value,

            'assignees' => MessageAssigneeResource::collection(
                $this->whenLoaded('assignees')
            ),

            'created_by' => $this->whenLoaded('createdBy', fn() => 
            $this->createdBy?->first_name . ' ' . $this->createdBy?->last_name . ' (' . $this->createdBy?->user_name . ')'
            )
        ];
    }
}
