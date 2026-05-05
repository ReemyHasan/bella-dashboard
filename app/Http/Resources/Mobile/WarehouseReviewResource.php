<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseReviewResource extends JsonResource
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
            'comment' => $this->comment,
            'rating' => $this->rating,
            'reviewer' => $this->whenLoaded('reviewer', fn() => [
                'id' => $this->reviewer?->id,
                'name' => $this->reviewer?->first_name . ' ' . $this->reviewer?->last_name,
            ])
        ];
    }
}
