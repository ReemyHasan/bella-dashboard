<?php

namespace App\Http\Resources\DashUser\Orders;

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
            'status' => $this->status,
            'start_at' => $this->start_at_formatted,
            'end_at' => $this->end_at_formatted,
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,

            'created_by' => $this->whenLoaded('createdBy', fn() => [
                'id' => $this->createdBy?->id,
                'type' => get_class($this->createdBy) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                'name' => $this->createdBy?->first_name . ' ' . $this->createdBy?->last_name . ' (' . $this->createdBy?->user_name . ')',
            ]),

            'co_created_by' => $this->whenLoaded('coCreatedBy', fn() => [
                'id' => $this->coCreatedBy?->id,
                'type' => get_class($this->coCreatedBy) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                'name' => $this->coCreatedBy?->first_name . ' ' . $this->coCreatedBy?->last_name . ' (' . $this->coCreatedBy?->user_name . ')',
            ]),

            // 🔹 Zones
            'zones' => $this->whenLoaded(
                'zones',
                fn() =>
                $this->zones->map(fn($zone) => [
                    'id' => $zone->id,
                    'name' => $zone->name ?? null,
                ])
            ),

            // 🔹 Teams
            'teams' => $this->whenLoaded(
                'teams',
                fn() =>
                $this->teams->map(fn($team) => [
                    'id' => $team->id,
                    'name' => $team->name ?? null,
                ])
            ),

            // 🔹 Subteams
            'subteams' => $this->whenLoaded(
                'subteams',
                fn() =>
                $this->subteams->map(fn($subteam) => [
                    'id' => $subteam->id,
                    'name' => $subteam->name ?? null,
                ])
            ),

            // 🔹 Marketers
            'marketers' => $this->whenLoaded(
                'marketers',
                fn() =>
                $this->marketers->map(fn($user) => [
                    'id' => $user->id,
                    'name' => $user?->first_name . ' ' . $user?->last_name . ' (' . $user?->user_name . ')',

                ])
            ),

            // 🔹 Products (with pivot 🔥)
            'products' => $this->whenLoaded(
                'products',
                fn() =>
                $this->products->map(fn($product) => [
                    'id' => $product->id,
                    'name' => $product->name ?? null,
                    'target_quantity' => $product->pivot?->target_quantity,
                ])
            ),

            // 🔹 Offers (with pivot 🔥)
            'offers' => $this->whenLoaded(
                'offers',
                fn() =>
                $this->offers->map(fn($offer) => [
                    'id' => $offer->id,
                    'name' => $offer->name ?? null,
                    'target_quantity' => $offer->pivot?->target_quantity,
                ])
            ),

            // 🔹 Winners
            'winners' => $this->whenLoaded(
                'winners',
                fn() =>
                $this->winners->map(fn($winner) => [
                    'id' => $winner->winner->id,
                    'name' => $winner->winner?->first_name . ' ' . $winner->winner?->last_name . ' (' . $winner->winner?->user_name . ')',
                    'achieved_value' => $winner->achieved_value ?? null
                ])
            ),
        ];
    }
}
