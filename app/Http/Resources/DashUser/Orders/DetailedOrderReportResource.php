<?php

namespace App\Http\Resources\DashUser\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DetailedOrderReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $marketer = $this->marketer;
        $warehouseMan = $this->warehouseMan;

        return [
            'order_number' => $this->order_number,

            'customer' =>
            optional($this->customer)->first_name . ' ' .
                optional($this->customer)->last_name,

            'address' => $this->address?->name,

            'customer_mobile' => $this->customer_mobile,
            'total_price' => $this->total_price,
            'delivery_cost' => $this->delivery_cost,
            'currency' => $this->currency?->symbol,

            'marketer' =>
            optional($marketer)->first_name . ' ' .
                optional($marketer)->last_name .
                ' (' . optional($marketer)->user_name . ')',

            'marketer_team' =>  $marketer->subTeam?->team?->name ?? $marketer->team?->name,
            'marketer_subteam' => $marketer->subTeam?->name,

            'products' => $this->products->map(fn($item) => [
                'product_name' => $item->product?->name,
                'quantity' => $item->quantity,
                'total_price' => $item->total_price
            ]),

            'offers' => $this->offers->map(fn($item) => [
                'offer_name' => $item->offer?->name,
                'quantity' => $item->quantity,
                'total_price' => $item->total_price
            ]),

            'warehouse_man' =>
            optional($warehouseMan)->first_name . ' ' .
                optional($warehouseMan)->last_name .
                ' (' . optional($warehouseMan)->user_name . ')',

            'warehouse_man_mobile' => $warehouseMan->mobile ?? null,

            'tips' => $this->additional_tips,
        ];
    }
}
