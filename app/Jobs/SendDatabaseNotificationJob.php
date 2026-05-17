<?php

namespace App\Jobs;

use App\Models\AppUser;
use App\Services\Notification\NotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendDatabaseNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $body,
        public array $userIds,
        public array $data,
    ) {}

    public function handle(NotificationService $notificationService): void
    {
        $users = AppUser::whereIn('id', $this->userIds)->get();
        $rows = [];

        foreach ($users as $user) {
            $rows[] = [
                'type' => $this->type,
                'title' => $this->title,
                'body' => $this->body,
                'notifiable_id'   => $user->id,
                'notifiable_type' => get_class($user),
                'data' => json_encode($this->data),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }
        $notificationService->insertNotification(
            $rows
        );
    }
}
