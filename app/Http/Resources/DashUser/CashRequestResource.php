<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashRequestResource extends JsonResource
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
            'requested_amount' => $this->requested_amount,
            'approved_amount' => $this->approved_amount,

            'delivery_cost' => $this->delivery_cost,
            // 'additional_delivery_cost' => $this->additional_delivery_cost,

            'status' => $this->status,
            'notes' => $this->notes,
            'cash_request_reason' => $this->cash_request_reason,

            'address_details' => $this->address_details,
            'address' => $this->whenLoaded('address', fn() => [
                'id' => $this->address?->id,
                'name' => $this->address?->name,
            ]),


            'from_vault' => $this->fromVault?->id == 1 ? [
                'id' => $this->fromVault?->id,
                'name' => 'خزنة الشركة'
            ] : $this->whenLoaded('fromVault', fn() => [
                'id' => $this->fromVault?->id,
                'name' => $this->fromVault?->owner?->first_name . ' ' . $this->fromVault?->owner?->last_name . ' (' . $this->fromVault?->owner?->user_name . ')',
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

            'delivered_by' => $this->whenLoaded('deliveredBy', fn() => [
                'id' => $this->deliveredBy?->id,
                'type' => get_class($this->deliveredBy) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                'name' => $this->deliveredBy?->first_name . ' ' . $this->deliveredBy?->last_name . ' (' . $this->deliveredBy?->user_name . ')',
            ]),

            'currency' => $this->whenLoaded('currency', fn() => [
                'id' => $this->currency?->id,
                'name' => $this->currency?->name,
            ]),

            "current_exchange_value" => $this->current_exchange_value,
            'payment_method' => $this->whenLoaded('paymentMethod', fn() => [
                'id' => $this->paymentMethod?->id,
                'name' => $this->paymentMethod?->name_ar . '-' . $this->paymentMethod?->name_en
            ]),
            'delivered_at' => $this->delivered_at_formatted,

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
