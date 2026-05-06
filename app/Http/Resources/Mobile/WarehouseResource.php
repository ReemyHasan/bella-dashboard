<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WarehouseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = auth()->user();
        if ($user->hasRole('Team Manager') || $user->hasRole('Team Leader') || $user->is_warehouse_man) {

            $warehouseKeeper =   $this->whenLoaded('keeper', fn() => [
                'id' => $this->keeper?->id,
                'name' => $this->keeper?->first_name . ' ' . $this->keeper?->last_name . ' (' . $this->keeper?->mobile . ')',
            ]);
        } else {
            $warehouseKeeper =   $this->whenLoaded('keeper', fn() => [
                'id' => $this->keeper?->id,
                'name' => $this->keeper?->first_name . ' ' . $this->keeper?->last_name,
            ]);
        }

        return [
            'id'          => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at_formatted,
            'active' =>  $this->active,
            'is_main' =>  $this->is_main,
            'zone' => $this->whenLoaded('zone', fn() => [
                'id' => $this->zone?->id,
                'name' => $this->zone?->name,
            ]),

            'keeper' => $warehouseKeeper
        ];
    }
}
