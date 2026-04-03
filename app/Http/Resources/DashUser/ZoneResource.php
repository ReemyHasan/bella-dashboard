<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ZoneResource extends JsonResource
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
            'currency' => $this->whenLoaded('currency', fn() => [
                'id' => $this->currency?->id,
                'name' => $this->currency?->name,
            ]),
            'created_at' => $this->created_at_formatted,

            'tips'         => $this->whenLoaded("tips", function () {
                return $this->tips->map(function ($tip) {
                    return [
                        'id'   => $tip->id,
                        'amount' => $tip->amount,
                    ];
                });
            }),
            'cities'         => $this->whenLoaded("cities", function () {
                return $this->cities->map(function ($city) {
                    return [
                        'id'   => $city->id,
                        'name' => $city->name,
                    ];
                });
            }),
        ];
    }
}
