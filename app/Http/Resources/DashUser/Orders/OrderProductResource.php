<?php

namespace App\Http\Resources\DashUser\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
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
            'quantity'          => $this->quantity,
            'unit_price'          => $this->unit_price,
            'total_price'          => $this->total_price,

            'product' => $this->whenLoaded('product', fn() => [
                'id' => $this->product?->id,
                'name' => $this->product?->name,

            ]),
        ];
    }
}
