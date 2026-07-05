<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\DashUser\ProductZonePriceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this?->product?->id,
            'name' => $this?->product?->name,
            'slug' => $this?->product?->slug,
            'size' => $this?->product?->size,

            'country_of_origin' => $this?->product?->country_of_origin,

            'active' => $this?->product?->active,

            'main_category' => $this->whenLoaded('product', function () {
                return $this->product->mainCategory ? [
                    'id' => $this->product->mainCategory->id,
                    'name' => $this->product->mainCategory->name,
                ] : null;
            }),

            'sub_category' => $this?->whenLoaded('product', function () {
                return [
                    'id' => $this?->product?->subCategory->id,
                    'name' => $this?->product?->subCategory->name,
                ];
            }),


            'main_image' => $this?->whenLoaded('product', function () {
                return $this->product->mainImage ? getPublicFileUrl($this?->product?->mainImage?->path) : null;
            }),

            'zone_prices' => $this?->whenLoaded('product', function () {
                return $this->product->zonePrices ? ProductZonePriceResource::collection($this->product->zonePrices) : null;
            }),
            'quantity' => $this->quantity,
            'reserved' => $this->reserved_quantity,
            'available' => $this->quantity - $this->reserved_quantity
        ];
    }
}
