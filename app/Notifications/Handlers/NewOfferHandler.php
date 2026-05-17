<?php

namespace App\Notifications\Handlers;

use App\Enums\DashUserStatus;
use App\Events\NotificationEvent;
use App\Jobs\SendDatabaseNotificationJob;
use App\Jobs\SendFirebaseNotificationJob;
use App\Models\AppUser;

class NewOfferHandler
{
    public function handleDatabase(NotificationEvent $event): void
    {
        $offer = $event->data['offer'];

        $users = $this->resolveUsers();

        $users
            ->chunkById(20, function ($chunkUsers) use ($event, $offer) {

                SendDatabaseNotificationJob::dispatch(
                    $event->type->value,
                    $event->type->label(),
                    $offer->name,
                    $chunkUsers->pluck('id')->toArray(),
                    [
                        'offer_id' => (string) $offer->id
                    ]
                );
                // ->onQueue('database-notifications');
            });

        // foreach ($users as $user) {

        //     $this->notificationService->createNotification(
        //         type: $event->type->value,
        //         client: $user,
        //         title: $event->type->label(),
        //         body: $offer->name,
        //         data: [
        //             'offer_id' => (string) $offer->id,
        //         ]
        //     );
        // }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        $offer = $event->data['offer'];


        $users = $this->resolveUsers();

        $users
            ->chunkById(20, function ($chunkUsers) use ($event, $offer) {

                SendFirebaseNotificationJob::dispatch(
                    $event->type->value,
                    $event->type->label(),
                    $offer->name,
                    $chunkUsers->pluck('id')->toArray(),
                    [
                        'type' => $event->type->value,
                        'offer_id' => (string) $offer->id
                    ]
                );
                // ->onQueue('firebase-notifications');
            });
        // foreach ($users as $user) {

        //     if (!$user->fcm_token) {
        //         continue;
        //     }

        //     $this->firebaseNotificationService->sendNotification(
        //         tokens: $user->fcm_token,
        //         title: $event->type->label(),
        //         body: $offer->name,
        //         data: [
        //             'type' => $event->type->value,
        //             'offer_id' => (string) $offer->id,
        //         ]
        //     );
        // }
    }

    private function resolveUsers()
    {
        return AppUser::query()->where('status', DashUserStatus::ACTIVE->value);
    }
}
