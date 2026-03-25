<?php

namespace App\Http\Resources\DashUser\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderStatusLogResource extends JsonResource
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
            'status'          => $this->status,
            'notes'          => $this->notes,
            'created_at_formatted'          => $this->created_at_formatted,
            'changed_by' => $this->whenLoaded('changedBy', fn() => [
                'id' => $this->changedBy?->id,
                'name' => $this->changedBy?->first_name . ' ' . $this->changedBy?->last_name . ' (' . $this->changedBy?->user_name . ')',
            ]),

        ];
    }
}
