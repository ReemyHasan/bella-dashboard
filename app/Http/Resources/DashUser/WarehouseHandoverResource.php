<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseHandoverResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'status' => $this->status,

            'requester_warehouse' => $this->whenLoaded('requesterWarehouse', fn() => [
                'id' => $this->requesterWarehouse?->id,
                'name' => $this->requesterWarehouse?->name,
            ]),


            'provider_warehouse' => $this->whenLoaded('providerWarehouse', fn() => [
                'id' => $this->providerWarehouse?->id,
                'name' => $this->providerWarehouse?->name,
            ]),


            'items'         => $this->whenLoaded("items", function () {
                return $this->items->map(function ($item) {
                    return [
                        'item_id' => $item->id,
                        'product_id' => $item->product_id,
                        'product' => $item?->product?->name . "-" . $item?->product?->mainCategory?->name . "-" .
                            $item?->product?->subCategory?->name . "-" . $item?->product?->size . "-" . $item?->product?->country_of_origin,
                        'requested_quantity' => $item->requested_quantity,
                        'approved_quantity' => $item->approved_quantity,
                        'delivered_quantity' => $item->delivered_quantity
                    ];
                });
            }),

            'notes' => $this->notes,

            'requester' => $this->whenLoaded('requester', fn() => [
                'id' => $this->requester?->id,
                'name' => $this->requester?->first_name . ' ' . $this->requester?->last_name . ' (' . $this->requester?->user_name . ')',
            ]),
            'responder' => $this->whenLoaded('responder', fn() => [
                'id' => $this->responder?->id,
                'name' => $this->responder?->first_name . ' ' . $this->responder?->last_name . ' (' . $this->responder?->user_name . ')',
            ]),
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'approved_at' => $this->approved_at_formatted,
            'completed_at' => $this->completed_at_formatted,

        ];
    }
}
