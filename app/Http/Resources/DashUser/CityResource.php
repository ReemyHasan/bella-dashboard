<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
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
            'symbol' => $this->symbol,
            'created_at' => $this->created_at_formatted,
            'zone' => $this->whenLoaded('zone', fn() => [
                'id' => $this->zone?->id,
                'name' => $this->zone?->name,
            ]),

        ];
    }
}
