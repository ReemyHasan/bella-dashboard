<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OfferResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'name' => $this->name,
            'symbol' => $this->symbol,

            'description' => $this->description,
            'summary' => $this->summary,
            'marketing_description' => $this->marketing_description,

            'active' => $this->active,



            'images' => ProductImageResource::collection(
                $this->whenLoaded('images')
            ),


            'tags' => TagResource::collection(
                $this->whenLoaded('tags')
            ),


            'zone_prices' => ProductZonePriceResource::collection(
                $this->whenLoaded('zonePrices')
            ),
            'warehouses' => OfferWarehouseResource::collection(
                $this->whenLoaded('offerWarehouses')
            ),

            'products' => OfferProductResource::collection(
                $this->whenLoaded('offerProducts')
            ),
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,

        ];
    }
}
