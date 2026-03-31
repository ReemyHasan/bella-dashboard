<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductZonePriceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'zone' => $this->whenLoaded('zone', function () {
                return [
                    'id' => $this->zone->id,
                    'name' => $this->zone->name,
                    'symbol' => $this->zone->symbol,
                    'currency' => $this->zone?->currency?->symbol,
                ];
            }),

            'price' => $this->price,
            'is_available' => $this->is_available,
        ];
    }
}
