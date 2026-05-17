<?php

namespace App\Notifications\Handlers;

use App\Enums\OrderStatus;
use App\Events\NotificationEvent;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Notification\NotificationService;

class OrderStatusChangeHandler
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FirebaseNotificationService $firebaseNotificationService,
    ) {}
    public function handleDatabase(NotificationEvent $event): void
    {
        ['users' => $users, 'order' => $order, 'status' => $status]
            = $this->resolveData($event);

        foreach ($users as $user) {

            $this->notificationService->createNotification(
                type: $event->type->value,
                client: $user,
                title: $event->type->label(),
                body: "تم تغيير حالة الطلب #{$order->order_number} إلى {$status->label()}",
                data: [
                    'order_id' => $order->id,
                    'status' => $status->value,
                ]
            );
        }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        ['users' => $users, 'order' => $order, 'status' => $status]
            = $this->resolveData($event);

        foreach ($users as $user) {

            if (!$user->fcm_token) {
                continue;
            }

            $this->firebaseNotificationService->sendNotification(
                tokens: $user->fcm_token,
                title: $event->type->label(),
                body: "تم تغيير حالة الطلب #{$order->order_number} إلى {$status->label()}",
                data: [
                    'type' => $event->type->value,
                    'order_id' => (string) $order->id,
                    'status' => $status->value,
                ]
            );
        }
    }

    private function resolveData(NotificationEvent $event): array
    {
        $order = $event->data['order'];

        /** @var OrderStatus $status */
        $status = $event->data['new_status'];

        $users = collect([
            $order->marketer,
        ]);

        // warehouse keeper notification statuses
        if ($this->shouldNotifyWarehouseKeeper($status)) {
            $users->push($order->warehouseMan);
        }

        return [
            'users' => $users
                ->filter()
                ->unique('id'),

            'order' => $order,
            'status' => $status,
        ];
    }

    private function shouldNotifyWarehouseKeeper(OrderStatus $status): bool
    {
        return in_array($status, [
            OrderStatus::new,
            OrderStatus::refund,
            OrderStatus::cancelled

        ]);
    }
}
