<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class RegionResource extends JsonResource
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
            'city' => $this->whenLoaded('city', fn() => [
                'id' => $this->city?->id,
                'name' => $this->city?->name,
            ]),

            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse?->id,
                'name' => $this->warehouse?->name,
            ]),

        ];
    }
}
