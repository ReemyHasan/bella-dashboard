<?php

namespace App\Http\Resources\Mobile;

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

            'status' => $this->status,
            'notes' => $this->notes,
            'cash_request_reason' => $this->cash_request_reason,

            'address_details' => $this->address_details,
            'address' => $this->whenLoaded('address', fn() => [
                'id' => $this->address?->id,
                'name' => $this->address?->name,
            ]),


            'delivery_man' => $this->fromVault?->id == 1 ?  'خزنة الشركة'
                : $this->whenLoaded(
                    'fromVault',
                    fn() =>
                    $this->fromVault?->owner?->first_name . ' ' . $this->fromVault?->owner?->last_name . ' (' . $this->fromVault?->owner?->user_name . ')'
                ),

            'requested_by' => $this->whenLoaded(
                'requestedBy',
                fn() =>
                $this->requestedBy?->first_name . ' ' . $this->requestedBy?->last_name . ' (' . $this->requestedBy?->user_name . ')'
            ),

            'requested_for' => $this->whenLoaded('requestedFor', fn() => $this->requestedFor?->first_name . ' ' . $this->requestedFor?->last_name . ' (' . $this->requestedFor?->user_name . ')'),

            'delivered_by' => $this->whenLoaded('deliveredBy', fn() =>
            $this->deliveredBy?->first_name . ' ' . $this->deliveredBy?->last_name . ' (' . $this->deliveredBy?->user_name . ')'),

            'currency' => $this->whenLoaded('currency', fn() => [
                'id' => $this->currency?->id,
                'name' => $this->currency?->name,
            ]),

            "current_exchange_value" => $this->current_exchange_value,
            'payment_method' => $this->whenLoaded('paymentMethod', fn() => [
                'id' => $this->paymentMethod?->id,
                'name' => $this->paymentMethod?->name_ar . '-' . $this->paymentMethod?->name_en
            ]),
            'payment_method_fields' => collect($this->payment_method_fields)
                ->map(function ($value, $key) {

                    $paymentMethod = $this->paymentMethod;

                    $field = collect($paymentMethod?->required_fields)
                        ->firstWhere('key', $key);

                    if (($field['type'] ?? null) === 'image') {
                        return getPublicFileUrl($value);
                    }

                    return $value;
                }),


            'delivered_at' => $this->delivered_at_formatted,

            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'reviewed_at' => $this->reviewed_at_formatted,
            'review_notes' => $this->review_notes,


            'reviewed_by' => $this->whenLoaded('reviewedBy', fn() =>
            $this->reviewedBy?->first_name . ' ' . $this->reviewedBy?->last_name . ' (' . $this->reviewedBy?->user_name . ')'),




        ];
    }
}
