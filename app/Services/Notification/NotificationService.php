<?php

namespace App\Services\Notification;

use App\Models\Notification;
use Illuminate\Support\Facades\Log;

class NotificationService
{
    public function createNotification(
        string $type,
        $client,
        string $title,
        string $body,
        array $data = []
    ) {
        try {
            return Notification::create([
                'type' => $type,
                'title' => $title,
                'body' => $body,
                'notifiable_id'   => $client->id,
                'notifiable_type' => get_class($client),
                'data' => json_encode($data)
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to create notification', [
                'error' => $e->getMessage(),
                'type' => $type,
                'actor' => "id:$client->id#$client->fcm_token",
            ]);
            throw $e;
        }
    }

    public function insertNotification(
        array $rows = []
    ) {
        try {
            Notification::insert($rows);
        } catch (\Throwable $e) {
            Log::error('Failed to create notification', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
