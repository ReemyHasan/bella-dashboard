<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'name' => $this->name,
            'slug' => $this->slug,
            'size' => $this->size,

            'description' => $this->description,
            'how_to_use' => $this->how_to_use,
            'precautions' => $this->precautions,
            'country_of_origin' => $this->country_of_origin,

            'active' => $this->active,

            'main_category' => $this->whenLoaded('mainCategory', function () {
                return [
                    'id' => $this->mainCategory->id,
                    'name' => $this->mainCategory->name,
                ];
            }),

            'sub_category' => $this->whenLoaded('subCategory', function () {
                return [
                    'id' => $this->subCategory->id,
                    'name' => $this->subCategory->name,
                ];
            }),


            'main_image' => $this->whenLoaded('mainImage', function () {
                return $this->mainImage?->path;
            }),

            'images' => ProductImageResource::collection(
                $this->whenLoaded('images')
            ),


            'tags' => TagResource::collection(
                $this->whenLoaded('tags')
            ),


            'zone_prices' => ProductZonePriceResource::collection(
                $this->whenLoaded('zonePrices')
            ),

            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'warehouse_quantity' => $this->when(
                isset($this->warehouse_quantity),
                (int) $this->warehouse_quantity
            ),
        ];
    }
}
