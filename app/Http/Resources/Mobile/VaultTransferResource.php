<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VaultTransferResource extends JsonResource
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

            'from_vault' => $this->fromVault?->id == 1 ?
                'خزنة الشركة'
                : $this->whenLoaded('fromVault', fn() =>
                $this->fromVault?->owner?->first_name . ' ' . $this->fromVault?->owner?->last_name . ' (' . $this->fromVault?->owner?->user_name . ')'),
            'to_vault' => $this->toVault?->id == 1 ?
                'خزنة الشركة'
                : $this->whenLoaded(
                    'toVault',
                    fn() =>
                    $this->toVault?->owner?->first_name . ' ' . $this->toVault?->owner?->last_name . ' (' . $this->toVault?->owner?->user_name . ')'
                ),


            'created_by' => $this->whenLoaded('createdBy', fn() =>
            $this->createdBy?->first_name . ' ' . $this->createdBy?->last_name . ' (' . $this->createdBy?->user_name . ')'),

            'created_at' => $this->created_at_formatted,
            'updated_at' => $this->updated_at_formatted,
            'transferred_at' => $this->transferred_at_formatted,

        ];
    }
}
