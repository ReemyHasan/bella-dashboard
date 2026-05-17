<?php

namespace App\Notifications\Handlers;

use App\Events\NotificationEvent;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Notification\NotificationService;

class NewMarketerHandler
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FirebaseNotificationService $firebaseNotificationService,
    ) {}
    public function handleDatabase(NotificationEvent $event): void
    {
        ['users' => $users, 'marketer' => $marketer]
            = $this->resolveData($event);

        foreach ($users as $user) {

            $this->notificationService->createNotification(
                type: $event->type->value,
                client: $user,
                title: $event->type->label(),
                body: "تم إضافة مسوق جديد {$marketer->first_name} {$marketer->last_name}",
                data: [
                    'marketer_id' => (string) $marketer->id,
                ]
            );
        }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        ['users' => $users, 'marketer' => $marketer]
            = $this->resolveData($event);

        foreach ($users as $user) {

            if (!$user->fcm_token) {
                continue;
            }

            $this->firebaseNotificationService->sendNotification(
                tokens: $user->fcm_token,
                title: $event->type->label(),
                body: "تم إضافة مسوق جديد {$marketer->first_name} {$marketer->last_name}",
                data: [
                    'type' => $event->type->value,
                    'marketer_id' => (string) $marketer->id,
                ]
            );
        }
    }

    private function resolveData(NotificationEvent $event): array
    {
        $marketer = $event->data['marketer'];

        $users = collect([
            optional($marketer->team)->manager,
            optional($marketer->subTeam)->teamLeader,
        ])
            ->filter()
            ->unique('id');

        return [
            'users' => $users,
            'marketer' => $marketer,
        ];
    }
}
