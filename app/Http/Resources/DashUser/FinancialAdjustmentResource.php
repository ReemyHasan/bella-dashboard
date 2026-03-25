<?php

namespace App\Http\Resources\DashUser;

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


            'from_vault' => $this->fromVault?->id == 1 ? [
                'id' => $this->fromVault?->id,
                'name' => 'خزنة الشركة'
            ] : $this->whenLoaded('fromVault', fn() => [
                'id' => $this->fromVault?->id,
                'name' => $this->fromVault?->owner?->first_name . ' ' . $this->fromVault?->owner?->last_name . ' (' . $this->fromVault?->owner?->user_name . ')',
            ]),
            'to_vault' => $this->toVault?->id == 1 ? [
                'id' => $this->toVault?->id,
                'name' => 'خزنة الشركة'
            ] : $this->whenLoaded('toVault', fn() => [
                'id' => $this->toVault?->id,
                'name' => $this->toVault?->owner?->first_name . ' ' . $this->toVault?->owner?->last_name . ' (' . $this->toVault?->owner?->user_name . ')',
            ]),

            'requested_by' => $this->whenLoaded('requestedBy', fn() => [
                'id' => $this->requestedBy?->id,
                'type' => get_class($this->requestedBy) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                'name' => $this->requestedBy?->first_name . ' ' . $this->requestedBy?->last_name . ' (' . $this->requestedBy?->user_name . ')',
            ]),

            'requested_for' => $this->whenLoaded('requestedFor', fn() => [
                'id' => $this->requestedFor?->id,
                'type' => get_class($this->requestedFor) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                'name' => $this->requestedFor?->first_name . ' ' . $this->requestedFor?->last_name . ' (' . $this->requestedFor?->user_name . ')',
            ]),

            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'reviewed_at' => $this->reviewed_at_formatted,
            'review_notes' => $this->review_notes,


            'reviewed_by' => $this->whenLoaded('reviewedBy', fn() => [
                'id' => $this->reviewedBy?->id,
                'name' => $this->reviewedBy?->first_name . ' ' . $this->reviewedBy?->last_name . ' (' . $this->reviewedBy?->user_name . ')',
            ])
        ];
    }
}
