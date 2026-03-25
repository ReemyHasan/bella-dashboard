<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferWarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this?->warehouse?->id,
            'name' => $this?->warehouse?->name,
            'quantity' => $this->quantity,
            'reserved' => $this->reserved_quantity,
            'available' => $this->quantity - $this->reserved_quantity
        ];
    }
}
