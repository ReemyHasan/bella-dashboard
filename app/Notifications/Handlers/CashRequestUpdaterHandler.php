<?php

namespace App\Notifications\Handlers;

use App\Enums\CashRequestStatus;
use App\Events\NotificationEvent;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Notification\NotificationService;

class CashRequestUpdaterHandler
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FirebaseNotificationService $firebaseNotificationService,
    ) {}
    public function handleDatabase(NotificationEvent $event): void
    {
        $data = $this->resolveData($event);

        foreach ($data['users'] as $user) {

            $this->notificationService->createNotification(
                type: $event->type->value,
                client: $user,
                title: $event->type->label(),
                body: $data['body'],
                data: $data['payload']
            );
        }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        $data = $this->resolveData($event);

        foreach ($data['users'] as $user) {

            if (!$user->fcm_token) {
                continue;
            }

            $this->firebaseNotificationService->sendNotification(
                tokens: $user->fcm_token,
                title: $event->type->label(),
                body: $data['body'],
                data: $data['payload']
            );
        }
    }

    private function resolveData(NotificationEvent $event): array
    {
        $cashRequest = $event->data['cash_request'];

        /** @var CashRequestStatus $newStatus */
        $newStatus = $event->data['new_status'];

        $users = collect([
            $cashRequest->requestedFor,
        ]);

        // notify warehouse keeper on approval
        if ($newStatus === CashRequestStatus::APPROVED) {

            $warehouseKeeper = $cashRequest->deliveredBy;

            if ($warehouseKeeper) {
                $users->push($warehouseKeeper);
            }
        }

        return [

            'users' => $users
                ->filter()
                ->unique('id'),

            'body' =>
            "تم تحديث حالة طلب رصيد إلى {$newStatus->label()}",

            'payload' => [
                'type' => $event->type->value,
                'cash_request_id' => (string) $cashRequest->id,
                'status' => $newStatus->value,
            ]
        ];
    }
}
