<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppUserRequestResource extends JsonResource
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
            'content' => $this->content,
            'appUser' => $this->whenLoaded('appUser', fn() => [
                'id' => $this->appUser?->id,
                'name' => $this->appUser?->first_name . ' ' . $this->appUser?->last_name,
            ]),
            'userRequestType' => $this->whenLoaded('userRequestType', fn() => [
                'id' => $this->userRequestType?->id,
                'name' => $this->userRequestType?->name
            ]),
            'status' => $this->status,
            'requested_by' => $this->whenLoaded(
                'requestedBy',
                fn() =>
                $this->requestedBy?->first_name . ' ' . $this->requestedBy?->last_name
                // [
                //     'id' => $this->requestedBy?->id,
                //     // 'type' => get_class($this->requestedBy) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                //     'name' => $this->requestedBy?->first_name . ' ' . $this->requestedBy?->last_name ,
                // ]
            ),
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'read_at' => $this->read_at_formatted,
            'handled_at' => $this->handled_at_formatted,
            'reviewer_notes' => $this->notes,

            'reviewed_by' => $this->whenLoaded('reviewedBy', fn() => [
                'id' => $this->reviewedBy?->id,
                'name' => $this->reviewedBy?->first_name . ' ' . $this->reviewedBy?->last_name,
            ])
        ];
    }
}
