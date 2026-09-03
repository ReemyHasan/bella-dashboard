<?php

namespace App\Notifications\Handlers;

use App\Events\NotificationEvent;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Notification\NotificationService;

class UpdateWarehouseHandoverHandler
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FirebaseNotificationService $firebaseNotificationService,
    ) {}

    public function handleDatabase(NotificationEvent $event): void
    {
        ['recipients' => $recipients, 'handover' => $handover]
            = $this->resolveData($event);

        foreach ($recipients as $recipient) {

            $this->notificationService->createNotification(
                type: $event->type->value,
                client: $recipient['user'],
                title: $event->type->label(),
                body: $recipient['body'],
                data: [
                    'handover_id' => $handover->id,
                ]
            );
        }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        ['recipients' => $recipients, 'handover' => $handover]
            = $this->resolveData($event);

        foreach ($recipients as $recipient) {

            $user = $recipient['user'];

            if (!$user->fcm_token) {
                continue;
            }

            $this->firebaseNotificationService->sendNotification(
                tokens: $user->fcm_token,
                title: $event->type->label(),
                body: $recipient['body'],
                data: [
                    'type' => $event->type->value,
                    'handover_id' => (string) $handover->id,
                ]
            );
        }
    }

    private function resolveData(NotificationEvent $event): array
    {
        $handover = $event->data['handover'];

        $recipients = collect(
            [
                [
                    'user' => $handover->requesterWarehouse?->keeper,
                    'type' => 'requester',
                    'body' => 'تم تحديث طلب التسليم الخاص بمستودعكم.'
                ],
                [
                    'user' => $handover->providerWarehouse?->keeper,
                    'type' => 'provider',
                    'body' => 'تم تحديث طلب التسليم من مستودعكم.'
                ]
            ]
        )->filter(fn($recipient) => $recipient['user'])
            ->unique(fn($recipient) => $recipient['user']->id)->values();

        return [
            'recipients' => $recipients,
            'handover' => $handover,
        ];
    }
}
