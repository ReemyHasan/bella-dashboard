<?php

namespace App\Http\Resources\DashUser\Orders;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderTransactionResource extends JsonResource
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
            'balance_before' => $this->to_vault_balance_before,
            'balance_after' => $this->to_vault_balance_after,

            'type' => $this->type,

            'reference' => $this->whenLoaded('reference', fn() => [
                'id' => $this->reference?->id,
                'type' => class_basename($this->reference)
            ]),


            'action_by' => $this->whenLoaded('actionBy', fn() => [
                'id' => $this->actionBy?->id,
                'type' => get_class($this->actionBy) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                'name' => $this->actionBy?->first_name . ' ' . $this->actionBy?->last_name . ' (' . $this->actionBy?->user_name . ')',
            ]),

            'to_user' => $this->whenLoaded('balanceUser', fn() => [
                'id' => $this->balanceUser?->id,
                'type' => get_class($this->balanceUser) == 'App\Models\DashUser' ? 'Dashboard User' :  'Marketer',
                'name' => $this->balanceUser?->first_name . ' ' . $this->balanceUser?->last_name . ' (' . $this->balanceUser?->user_name . ')',
            ]),
            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'transaction_date' => $this->transaction_date_formatted,

        ];
    }
}
