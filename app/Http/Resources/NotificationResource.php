<?php

namespace App\Http\Resources;

use App\Enums\NotificationType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            'type_en' => $this->type,
            'type_ar' => NotificationType::from($this->type)->label(),
            'created_at' => diffForHumans($this->created_at),
            // 'created_date' => $this->created_at_formatted,
            'read_at' => $this->read_at ? diffForHumans($this->read_at) : null,
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data
        ];
    }
}
