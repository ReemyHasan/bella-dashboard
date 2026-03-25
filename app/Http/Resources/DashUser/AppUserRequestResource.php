<?php

namespace App\Http\Resources\DashUser;

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
                'name' => $this->appUser?->first_name . ' ' . $this->appUser?->last_name . ' (' . $this->appUser?->user_name . ')',
            ]),
            'userRequestType' => $this->whenLoaded('userRequestType', fn() => [
                'id' => $this->userRequestType?->id,
                'name' => $this->userRequestType?->name
            ]),

            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'read_at' => $this->read_at_formatted,
            'handled_at' => $this->handled_at_formatted,


        ];
    }
}
