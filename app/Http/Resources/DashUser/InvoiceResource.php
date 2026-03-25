<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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
            'title' => $this->title,
            'name_of_merchant' => $this->name_of_merchant,
            'date' => $this->date_formatted,
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'is_confirmed' =>  $this->is_confirmed,

            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse?->id,
                'name' => $this->warehouse?->name,
            ]),

            'invoice_product_warehouses' => InvoiceProductResource::collection(
                $this->whenLoaded('invoiceProductWarehouses')
            ),

        ];
    }
}
