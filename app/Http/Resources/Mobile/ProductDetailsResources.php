<?php

namespace App\Http\Resources\Mobile;

use App\Http\Resources\DashUser\ProductImageResource;
use App\Http\Resources\DashUser\ProductZonePriceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductDetailsResources extends JsonResource
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

            'main_category' => $this->whenLoaded('mainCategory', function () {
                return [
                    'id' => $this->mainCategory->id,
                    'name' => $this->mainCategory->name,
                ];
            }),

            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
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

            'tags'         => $this->whenLoaded("tags", function () {
                return $this->tags->map(function ($tag) {
                    return  $tag?->name;
                });
            }),



            'zone_prices' => ProductZonePriceResource::collection(
                $this->whenLoaded('zonePrices')
            ),
            'is_marked_from_manager' => $this->is_important

        ];
    }
}
