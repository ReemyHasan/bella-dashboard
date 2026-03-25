<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this?->product?->id,
            'name' => $this?->product?->name,

            // 'main_category' => $this->whenLoaded('product', function () {
            //     return $this->product->mainCategory ? [
            //         'id' => $this->product->mainCategory->id,
            //         'name' => $this->product->mainCategory->name,
            //     ] : null;
            // }),

            // 'sub_category' => $this?->whenLoaded('product', function () {
            //     return [
            //         'id' => $this?->product?->subCategory->id,
            //         'name' => $this?->product?->subCategory->name,
            //     ];
            // }),


            'main_image' => $this?->whenLoaded('product', function () {
                return $this->product->mainImage ? $this?->product?->mainImage?->path : null;
            }),

            'quantity' => $this->quantity
        ];
    }
}
