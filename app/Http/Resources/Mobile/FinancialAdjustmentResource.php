<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FinancialAdjustmentResource extends JsonResource
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
            'type' => $this->type,

            'reason' => $this->reason,

            'requested_by' => $this->whenLoaded('requestedBy', fn() => [
                'id' => $this->requestedBy?->id,
                'name' => $this->requestedBy?->first_name . ' ' . $this->requestedBy?->last_name,
            ]),

            'requested_for' => $this->whenLoaded('requestedFor', fn() => [
                'id' => $this->requestedFor?->id,
                'name' => $this->requestedFor?->first_name . ' ' . $this->requestedFor?->last_name . ' (' . $this->requestedFor?->user_name . ')',
            ]),

            'created_at' => $this->created_at_formatted,
            'reviewed_at' => $this->reviewed_at_formatted,
            'review_notes' => $this->review_notes,
            'reviewed_by' => $this->whenLoaded(
                'reviewedBy',
                fn() => $this->reviewedBy?->first_name . ' ' . $this->reviewedBy?->last_name
            )
        ];
    }
}
