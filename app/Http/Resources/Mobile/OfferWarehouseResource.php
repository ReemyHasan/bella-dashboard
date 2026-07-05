<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\DashUser\ProductZonePriceResource;
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
            'main_image' => $this?->whenLoaded('offer', function () {
                return $this->offer->mainImage ? getPublicFileUrl($this?->offer?->mainImage?->path) : null;
            }),
            'zone_prices' => $this?->whenLoaded('offer', function () {
                return $this->offer->zonePrices ? ProductZonePriceResource::collection($this->offer->zonePrices) : null;
            }),

        ];
    }
}
