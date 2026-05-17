<?php

namespace App\Notifications\Handlers;

use App\Events\NotificationEvent;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Notification\NotificationService;

class FinancialMovementHandler
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FirebaseNotificationService $firebaseNotificationService,
    ) {}
    public function handleDatabase(NotificationEvent $event): void
    {
        $data = $this->resolveData($event);

        $this->notificationService->createNotification(
            type: $event->type->value,
            client: $data['user'],
            title: $event->type->label(),
            body: $data['body'],
            data: $data['payload']
        );
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        $data = $this->resolveData($event);

        if (!$data['user']->fcm_token) {
            return;
        }

        $this->firebaseNotificationService->sendNotification(
            tokens: $data['user']->fcm_token,
            title: $event->type->label(),
            body: $data['body'],
            data: $data['payload']
        );
    }

    private function resolveData(NotificationEvent $event): array
    {
        $user = $event->data['user'];

        $oldBalance = $event->data['old_balance'];

        $newBalance = $event->data['new_balance'];

        $difference = $event->data['difference'];

        $operation = $difference >= 0
            ? 'إضافة'
            : 'خصم';

        return [

            'user' => $user,

            'body' =>
            "{$operation} رصيد بقيمة "
                . abs($difference)
                . " ، الرصيد الحالي {$newBalance}",

            'payload' => [
                'type' => $event->type->value,
                'old_balance' => (string) $oldBalance,
                'new_balance' => (string) $newBalance,
                'difference' => (string) $difference,
            ]
        ];
    }
}
