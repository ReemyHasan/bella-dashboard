<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
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
            'created_at' => $this->created_at_formatted,
            'delivery_man' => $this->whenLoaded('deliveryMan', fn() => [
                'id' => $this->deliveryMan?->id,
                'name' => $this->deliveryMan?->first_name . ' ' . $this->deliveryMan?->last_name . ' (' . $this->deliveryMan?->user_name . ')',
            ]),
            'alter_delivery_man' => $this->whenLoaded('alterDeliveryMan', fn() => [
                'id' => $this->alterDeliveryMan?->id,
                'name' => $this->alterDeliveryMan?->first_name . ' ' . $this->alterDeliveryMan?->last_name . ' (' . $this->alterDeliveryMan?->user_name . ')',
            ]),
            'region' => $this->whenLoaded('region', fn() => [
                'id' => $this->region?->id,
                'name' => $this->region?->name,
            ]),
            'city' => $this->whenLoaded('region', function () {
                if ($this->region?->relationLoaded('city') && $this->region->city) {
                    return [
                        'id' => $this->region->city->id,
                        'name' => $this->region->city->name,
                    ];
                }
                return null;
            }),

            'zone' => $this->whenLoaded('region', function () {
                if (
                    $this->region?->relationLoaded('city') &&
                    $this->region->city?->relationLoaded('zone') &&
                    $this->region->city->zone
                ) {
                    return [
                        'id' => $this->region->city->zone->id,
                        'name' => $this->region->city->zone->name,
                    ];
                }
                return null;
            }),
        ];
    }
}
