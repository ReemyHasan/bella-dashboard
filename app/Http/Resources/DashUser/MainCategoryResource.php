<?php

namespace App\Http\Resources\DashUser;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MainCategoryResource extends JsonResource
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
            'name' => $this->name,
            'created_at' => $this->created_at_formatted,
            'image_path' => getPublicFileUrl($this->image_path),
            'active' =>  $this->active
        ];
    }
}
