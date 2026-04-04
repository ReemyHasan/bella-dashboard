<?php

namespace App\Http\Resources\DashUser\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerOrderResource extends JsonResource
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
            'order_number' => $this->order_number,


            'customer_mobile' => $this->customer_mobile,
            'marketer_percentage' => $this->marketer_percentage,
            'delivery_cost' => $this->delivery_cost,
            // 'delivery_additional_cost' => $this->delivery_additional_cost,
            'teamleader_percentage' => $this->teamleader_percentage,
            'manager_percentage' => $this->manager_percentage,
            'current_exchange_rate' => $this->current_exchange_rate,

            'address_details' => $this->address_details,

            'notes' => $this->notes,


            'order_status' => $this->order_status,
            'total_base_price' => $this->total_base_price,

            'company_income' => $this->total_price,
            'additional_tips' => $this->additional_tips,
            'adjustment_type' => $this->adjustment_type,


            'adjustment_operation' => $this->adjustment_operation,
            'adjustment_value' => $this->adjustment_value,


            'final_total_price' => $this->final_total_price,
            'is_financial_processed' => $this->is_financial_processed,
            //'is_target' => $this->is_target,

            'marketer_amount_in_local_currency' => $this->marketer_amount,
            'teamleader_amount_in_local_currency' => $this->teamleader_amount,
            'manager_amount_in_local_currency' => $this->manager_amount,

            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            // 'placed_at' => $this->placed_at_formatted,
            'waiting_until' => $this->waiting_until_formatted,
            'waiting_reason' => $this->waiting_reason,

            'cancelled_at' => $this->cancelled_at_formatted,
            'cancellation_reason' => $this->cancellation_reason,

            'marketer' => $this->whenLoaded('marketer', fn() => [
                'id' => $this->marketer?->id,
                'name' => $this->marketer?->first_name . ' ' . $this->marketer?->last_name . ' (' . $this->marketer?->user_name . ')',
            ]),
            'warehouse_man' => $this->whenLoaded('warehouseMan', fn() => [
                'id' => $this->warehouseMan?->id,
                'name' => $this->warehouseMan?->first_name . ' ' . $this->warehouseMan?->last_name . ' (' . $this->warehouseMan?->user_name . ')',
            ]),

            'teamleader' => $this->whenLoaded('teamleader', fn() => [
                'id' => $this->teamleader?->id,
                'name' => $this->teamleader?->first_name . ' ' . $this->teamleader?->last_name . ' (' . $this->teamleader?->user_name . ')',
            ]),

            'manager' => $this->whenLoaded('manager', fn() => [
                'id' => $this->manager?->id,
                'name' => $this->manager?->first_name . ' ' . $this->manager?->last_name . ' (' . $this->manager?->user_name . ')',
            ]),

            'warehouse' => $this->whenLoaded('warehouse', fn() => [
                'id' => $this->warehouse?->id,
                'name' => $this->warehouse?->name,
            ]),

            'customer' => $this->whenLoaded('customer', fn() => [
                'id' => $this->customer?->id,
                'name' => $this->customer?->first_name . ' ' . $this->customer?->last_name . ' (' . $this->customer?->user_name . ')',
            ]),

            'address' => $this->whenLoaded('address', fn() => [
                'id' => $this->address?->id,
                'name' => $this->address?->name,
            ]),

            'currency' => $this->whenLoaded('currency', fn() => [
                'id' => $this->currency?->id,
                'name' => $this->currency?->name,
            ]),


            'created_by' => $this->whenLoaded('createdBy', fn() => [
                'id' => $this->createdBy?->id,
                'type' => get_class($this->createdBy) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                'name' => $this->createdBy?->first_name . ' ' . $this->createdBy?->last_name . ' (' . $this->createdBy?->user_name . ')',
            ]),

            'reviewed_at' => $this->reviewed_at_formatted,
            'reviewed_by' => $this->whenLoaded('reviewedBy', fn() => [
                'id' => $this->reviewedBy?->id,
                'name' => $this->reviewedBy?->first_name . ' ' . $this->reviewedBy?->last_name . ' (' . $this->reviewedBy?->user_name . ')',
            ]),


            'products' => OrderProductResource::collection(
                $this->whenLoaded('products')
            ),
            'offers' => OrderOfferResource::collection(
                $this->whenLoaded('offers')
            ),

            'status_logs' => OrderStatusLogResource::collection(
                $this->whenLoaded('statusLogs')
            ),
        ];
    }
}
