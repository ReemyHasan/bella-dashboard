<?php

namespace App\Notifications\Handlers;

use App\Events\NotificationEvent;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Notification\NotificationService;

class OrderNoteHandler
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FirebaseNotificationService $firebaseNotificationService,
    ) {}
    public function handleDatabase(NotificationEvent $event): void
    {
        ['users' => $users, 'order' => $order, 'notes' => $notes]
            = $this->resolveData($event);

        foreach ($users as $user) {

            $this->notificationService->createNotification(
                type: $event->type->value,
                client: $user,
                title: $event->type->label(),
                body: "تم إضافة ملاحظة جديدة على الطلب",
                data: [
                    'order_id' => $order->id,
                    'notes' => $notes
                ]
            );
        }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        ['users' => $users, 'order' => $order, 'notes' => $notes]
            = $this->resolveData($event);

        foreach ($users as $user) {

            if (!$user->fcm_token) {
                continue;
            }

            $this->firebaseNotificationService->sendNotification(
                tokens: $user->fcm_token,
                title: $event->type->label(),
                body: "تم إضافة ملاحظة جديدة على الطلب",
                data: [
                    'type' => $event->type->value,
                    'order_id' => (string) $order->id,
                    'notes' => $notes
                ]
            );
        }
    }

    private function resolveData(NotificationEvent $event): array
    {
        $order = $event->data['order'];
        $notes = $event->data['notes'];

        $users = collect([
            $order->marketer,
        ]);
        $users->push($order->warehouseMan);


        return [
            'users' => $users
                ->filter()
                ->unique('id'),

            'order' => $order,
            'notes' => $notes,

        ];
    }
}
