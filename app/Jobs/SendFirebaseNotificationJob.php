<?php

namespace App\Jobs;

use App\Models\AppUser;
use App\Services\Notification\FirebaseNotificationService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendFirebaseNotificationJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $type,
        public string $title,
        public string $body,
        public array $userIds,
        public array $data,
    ) {}

    public function handle(FirebaseNotificationService $firebaseNotificationService): void
    {
        $users = AppUser::whereIn('id', $this->userIds)->get();

        foreach ($users as $user) {
            if (!$user->fcm_token) {
                continue;
            }

            $firebaseNotificationService->sendNotification(
                tokens: $user->fcm_token,
                title: $this->title,
                body: $this->body,
                data: $this->data
            );
        }
    }
}
