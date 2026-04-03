<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaultTransactionResource extends JsonResource
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
            'notes' => $this->notes,
            'reason' => $this->reason,

            'from_vault_balance_before' => $this->from_vault_balance_before,
            'from_vault_balance_after' => $this->from_vault_balance_after,
            'to_vault_balance_before' => $this->to_vault_balance_before,
            'to_vault_balance_after' => $this->to_vault_balance_after,

            'direction' => $this->directionForVault($request->vault_id),
            'type' => $this->type,

            'reference' => $this->whenLoaded('reference', fn() => [
                'id' => $this->reference?->id,
                'type' => class_basename($this->reference)
            ]),
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
