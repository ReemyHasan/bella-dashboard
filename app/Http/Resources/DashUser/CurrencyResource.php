<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyResource extends JsonResource
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
            'is_main' => $this->is_main,
            'exchange_value' => $this->exchange_value,
            'created_at' => $this->created_at_formatted,
            'zones'         => $this->whenLoaded("zones", function () {
                return $this->zones->map(function ($zone) {
                    return [
                        'id'   => $zone->id,
                        'name' => $zone->name,
                    ];
                });
            }),

        ];
    }
}
