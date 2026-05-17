<?php

namespace App\Notifications\Handlers;

use App\Events\NotificationEvent;
use App\Jobs\SendDatabaseNotificationJob;
use App\Jobs\SendFirebaseNotificationJob;
use App\Models\AppUser;

class NewCompetitionHandler
{
    public function handleDatabase(NotificationEvent $event): void
    {
        $competition = $event->data['competition'];

        $users = $this->resolveUsers($competition);

        $users
            ->chunkById(20, function ($chunkUsers) use ($event, $competition) {

                SendDatabaseNotificationJob::dispatch(
                    $event->type->value,
                    $event->type->label(),
                    $competition->name,
                    $chunkUsers->pluck('id')->toArray(),
                    [
                        'competition_id' => $competition->id,
                    ]
                );
                // ->onQueue('database-notifications');;
            });

        // foreach ($users as $user) {

        //     $this->notificationService->createNotification(
        //         type: $event->type->value,
        //         client: $user,
        //         title: $event->type->label(),
        //         body: $competition->name,
        //         data: [
        //             'competition_id' => $competition->id,
        //         ]
        //     );
        // }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        $competition = $event->data['competition'];

        $users = $this->resolveUsers($competition);

        $users
            ->chunkById(20, function ($chunkUsers) use ($event, $competition) {

                SendFirebaseNotificationJob::dispatch(
                    $event->type->value,
                    $event->type->label(),
                    $competition->name,
                    $chunkUsers->pluck('id')->toArray(),
                    [
                        'type' => $event->type->value,
                        'competition_id' => (string) $competition->id,
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
        //         body: $competition->name,
        //         data: [
        //             'type' => $event->type->value,
        //             'competition_id' => (string) $competition->id,
        //         ]
        //     );
        // }
    }

    private function resolveUsers($competition)
    {
        return match ($competition->target) {

            'marketers' =>
            AppUser::query()->whereIn(
                'id',
                $competition->marketers->pluck('id')
            ),

            'teams' =>
            AppUser::query()->whereIn(
                'team_id',
                $competition->teams->pluck('id')
            ),

            'subteams' =>
            AppUser::query()->whereIn(
                'subteam_id',
                $competition->subteams->pluck('id')
            ),

            default => AppUser::query()->whereRaw('1 = 0'),
        };
    }
}
