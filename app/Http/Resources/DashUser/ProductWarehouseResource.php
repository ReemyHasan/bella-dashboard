<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;


class ProductWarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'warehouse_id' => $this->warehouse?->id,
            'warehouse_name' => $this->warehouse?->name,

            'quantity' => $this->quantity,
            'reserved' => $this->reserved_quantity,
            'available' => $this->quantity - $this->reserved_quantity,
        ];
    }
}
