<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this?->product?->name . "-" . $this?->product?->mainCategory?->name . "-" .
                $this?->product?->subCategory?->name . "-" . $this?->product?->size . "-" . $this?->product?->country_of_origin,
            'added_quantity' => $this->added_quantity
        ];
    }
}
