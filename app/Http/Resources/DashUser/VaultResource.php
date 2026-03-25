<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaultResource extends JsonResource
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
            'balance' => $this->balance,
            'owner' => $this->id == 1 ? [
                'id' => null,
                'name' => 'خزنة الشركة'
            ] : $this->whenLoaded('owner', fn() => [
                'id' => $this->owner?->id,
                'name' => $this->owner?->first_name . ' ' . $this->owner?->last_name . ' (' . $this->owner?->user_name . ')',
            ]),
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,

        ];
    }
}
