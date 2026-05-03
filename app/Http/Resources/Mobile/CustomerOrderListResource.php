<?php

namespace App\Http\Resources\Mobile;

use App\Models\DashUser;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        $flag = $user instanceof DashUser
            || $user->hasRole('Team Manager')
            || $user->hasRole('Team Leader');
        return [
            'id'          => $this->id,
            'order_number' => $this->order_number,
            'customer_mobile' => $this->customer_mobile,
            'order_status' => $this->order_status,
            'total_base_price' => $this->total_base_price,
            'final_total_price' => $this->final_total_price,
            'created_at' => $this->created_at_formatted,
            'marketer' => $this->whenLoaded('marketer', fn() => [
                'id' => $this->marketer?->id,
                'name' => $this->marketer?->first_name . ' ' . $this->marketer?->last_name . ' (' . $this->marketer?->user_name . ')',
            ]),

            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->first_name . ' ' . $this->customer?->last_name,
            ]),
            'warehouse_man' => $this->whenLoaded('warehouseMan', fn() => [
                'id' => $this->warehouseMan?->id,
                'name' => $this->warehouseMan?->first_name . ' ' . $this->warehouseMan?->last_name . ($flag ? ' (' . $this->warehouseMan?->mobile . ')' : ''),
            ]),
            'currency' => $this->whenLoaded('currency', fn() => [
                'id' => $this->currency?->id,
                'name' => $this->currency?->name,
            ]),
            'last_note' => $this->whenLoaded('lastStatusLog', fn() => $this->lastStatusLog?->notes),
        ];
    }
}
