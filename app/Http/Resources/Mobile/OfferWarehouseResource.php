<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferWarehouseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->offer?->id,
            'name' => $this->offer?->name,
            'symbol' => $this->offer?->symbol,
            'active' => $this->offer?->active,
        ];
    }
}
