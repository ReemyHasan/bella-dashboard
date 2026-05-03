<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerResource extends JsonResource
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
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'mobile' => $this->mobile,
            'is_blocked' => $this->is_blocked,
            'blocked_date' => $this->blocked_date_formatted,
            'blocked_reason' => $this->blocked_reason,
            'addresses'         => $this->whenLoaded("addresses", function () {
                return $this->addresses->map(function ($address) {
                    return [
                        'id' => $address->id,
                        'address' => $address->full_address ?? $address->name ?? '',
                        'extra_details' => $address->pivot->extra_details,
                        'is_main' => (bool)$address->pivot->is_main,
                    ];
                });
            }),

            'created_by' => $this->whenLoaded('createdBy', fn() => [
                'id' => $this->createdBy?->id,
                'name' => $this->createdBy?->first_name . ' ' . $this->createdBy?->last_name . ' (' . $this->createdBy?->user_name . ')',
            ]),
            'blocked_by' => $this->whenLoaded('blockedBy', fn() => [
                'id' => $this->blockedBy?->id,
                'name' => $this->blockedBy?->first_name . ' ' . $this->blockedBy?->last_name . ' (' . $this->blockedBy?->user_name . ')',
            ]),

            'updated_by' => $this->whenLoaded('updatedBy', fn() => [
                'id' => $this->updatedBy?->id,
                'name' => $this->updatedBy?->first_name . ' ' . $this->updatedBy?->last_name . ' (' . $this->updatedBy?->user_name . ')',
            ]),
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,

        ];
    }
}
