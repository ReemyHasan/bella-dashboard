<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BalanceTransferRequestResource extends JsonResource
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
            'amount' => $this->amount,
            'status' => $this->status,
            'notes' => $this->notes,

            'from_user' => $this->whenLoaded('fromUser', fn() => [
                'id' => $this->fromUser?->id,
                'name' => $this->fromUser?->first_name . ' ' . $this->fromUser?->last_name . ' (' . $this->fromUser?->user_name . ')',
            ]),

            'to_user' => $this->whenLoaded('toUser', fn() => [
                'id' => $this->toUser?->id,
                'name' => $this->toUser?->first_name . ' ' . $this->toUser?->last_name . ' (' . $this->toUser?->user_name . ')',
            ]),


            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'reviewed_at' => $this->reviewed_at_formatted,
            'review_notes' => $this->review_notes,


            'reviewed_by' => $this->whenLoaded('reviewedBy', fn() => [
                'id' => $this->reviewedBy?->id,
                'name' => $this->reviewedBy?->first_name . ' ' . $this->reviewedBy?->last_name . ' (' . $this->reviewedBy?->user_name . ')',
            ]),




        ];
    }
}
