<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompetitionResource extends JsonResource
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
            'description' => $this->description,
            'prize' => $this->prize,
            'type' => $this->type,
            'target' => $this->target,
            'target_value' => $this->target_value,
            'start_at' => $this->start_at_formatted,
            'end_at' => $this->end_at_formatted,
            // 🔹 Zones
            'zones' => $this->whenLoaded(
                'zones',
                fn() =>
                $this->zones->map(fn($zone) => [
                    'id' => $zone->id,
                    'name' => $zone->name ?? null,
                ])
            ),
            'products' => $this->whenLoaded(
                'products',
                fn() =>
                $this->products->map(fn($product) => [
                    'id' => $product->id,
                    'name' => $product->name ?? null,
                    'target_quantity' => $product->pivot?->target_quantity,
                ])
            ),
            'offers' => $this->whenLoaded(
                'offers',
                fn() =>
                $this->offers->map(fn($offer) => [
                    'id' => $offer->id,
                    'name' => $offer->name ?? null,
                    'target_quantity' => $offer->pivot?->target_quantity,
                ])
            ),
            "my_rank" => $this->my_rank ?? 0,
            "my_score" => $this?->my_score ?? 0

        ];
    }
}
