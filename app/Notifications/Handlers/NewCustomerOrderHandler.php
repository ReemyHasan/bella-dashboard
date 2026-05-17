<?php

namespace App\Notifications\Handlers;

use App\Events\NotificationEvent;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Notification\NotificationService;

class NewCustomerOrderHandler
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FirebaseNotificationService $firebaseNotificationService,
    ) {}
    public function handleDatabase(NotificationEvent $event): void
    {
        ['users' => $users, 'order' => $order]
            = $this->resolveData($event);

        foreach ($users as $user) {

            $this->notificationService->createNotification(
                type: $event->type->value,
                client: $user,
                title: $event->type->label(),
                body: "تم إنشاء طلب جديد رقم #{$order->order_number}",
                data: [
                    'order_id' => $order->id,
                ]
            );
        }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        ['users' => $users, 'order' => $order]
            = $this->resolveData($event);

        foreach ($users as $user) {

            if (!$user->fcm_token) {
                continue;
            }

            $this->firebaseNotificationService->sendNotification(
                tokens: $user->fcm_token,
                title: $event->type->label(),
                body: "تم إنشاء طلب جديد رقم #{$order->order_number}",
                data: [
                    'type' => $event->type->value,
                    'order_id' => (string) $order->id,
                ]
            );
        }
    }

    private function resolveData(NotificationEvent $event): array
    {
        $order = $event->data['order'];

        $users = collect([
            optional($order->team)->manager,
            optional($order->subTeam)->teamLeader,
        ])->filter();

        return [
            'users' => $users->unique('id'),
            'order' => $order,
        ];
    }
}
