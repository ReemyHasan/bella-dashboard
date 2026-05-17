<?php

namespace App\Notifications\Handlers;

use App\Enums\DashUserStatus;
use App\Events\NotificationEvent;
use App\Jobs\SendDatabaseNotificationJob;
use App\Jobs\SendFirebaseNotificationJob;
use App\Models\AppUser;

class DeductOnProductHandler
{
    public function handleDatabase(NotificationEvent $event): void
    {
        $product = $event->data['product'];

        $users = $this->resolveUsers();

        $users
            ->chunkById(20, function ($chunkUsers) use ($event, $product) {

                SendDatabaseNotificationJob::dispatch(
                    $event->type->value,
                    $event->type->label(),
                    "تم إضافة تعديل على سعر المنتج {$product->name}",
                    $chunkUsers->pluck('id')->toArray(),
                    [
                        'product_id' => (string) $product->id
                    ]
                )->onQueue('database-notifications');;
            });

        // foreach ($this->resolveUsers() as $user) {

        //     $this->notificationService->createNotification(
        //         type: $event->type->value,
        //         client: $user,
        //         title: $event->type->label(),
        //         body: "تم إضافة تعديل على سعر المنتج {$product->name}",
        //         data: [
        //             'product_id' => (string) $product->id
        //         ]
        //     );
        // }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        $product = $event->data['product'];

        $users = $this->resolveUsers();

        $users
            ->chunkById(20, function ($chunkUsers) use ($event, $product) {

                SendFirebaseNotificationJob::dispatch(
                    $event->type->value,
                    $event->type->label(),
                    "تم إضافة تعديل على سعر المنتج {$product->name}",
                    $chunkUsers->pluck('id')->toArray(),
                    [
                        'type' => $event->type->value,
                        'product_id' => (string) $product->id
                    ]
                )->onQueue('firebase-notifications');
            });

        // foreach ($this->resolveUsers() as $user) {

        //     if (!$user->fcm_token) {
        //         continue;
        //     }

        //     $this->firebaseNotificationService->sendNotification(
        //         tokens: $user->fcm_token,
        //         title: $event->type->label(),
        //         body: "تم إضافة تعديل على سعر المنتج {$product->name}",
        //         data: [
        //             'type' => $event->type->value,
        //             'product_id' => (string) $product->id
        //         ]
        //     );
        // }
    }

    private function resolveUsers()
    {
        return AppUser::query()->where('status', DashUserStatus::ACTIVE->value);
    }
}
