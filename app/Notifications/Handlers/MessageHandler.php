<?php

namespace App\Notifications\Handlers;

use App\Enums\DashUserStatus;
use App\Events\NotificationEvent;
use App\Jobs\SendDatabaseNotificationJob;
use App\Jobs\SendFirebaseNotificationJob;
use App\Models\AppUser;

class MessageHandler
{
    public function handleDatabase(NotificationEvent $event): void
    {
        $message = $event->data['message'];

        $users = $this->resolveUsers($message);

        $users
            ->chunkById(20, function ($chunkUsers) use ($event, $message) {

                SendDatabaseNotificationJob::dispatch(
                    $event->type->value,
                    $event->type->label(),
                    $message->description,
                    $chunkUsers->pluck('id')->toArray(),
                    [
                        'message_id' => $message->id,
                    ]
                )->onQueue('database-notifications');;
            });
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        $message = $event->data['message'];

        $users = $this->resolveUsers($message);

        $users
            ->chunkById(20, function ($chunkUsers) use ($event, $message) {

                SendFirebaseNotificationJob::dispatch(
                    $event->type->value,
                    $event->type->label(),
                    $message->description,
                    $chunkUsers->pluck('id')->toArray(),
                    [
                        'type' => $event->type->value,
                        'message_id' => (string) $message->id,
                    ]
                )->onQueue('firebase-notifications');
            });
    }

    private function resolveUsers($message)
    {
        if ($message->assignment_type === 'all') {
            return AppUser::query()->where('status', DashUserStatus::ACTIVE->value);
        }

        return match ($message->target_type->value) {

            'marketer' =>
            AppUser::query()->whereIn(
                'id',
                $message->assignees->pluck('marketer_id')
            ),

            'team' =>
            AppUser::query()->whereIn(
                'team_id',
                $message->assignees->pluck('team_id')
            ),

            'sub_team' =>
            AppUser::query()->whereIn(
                'subteam_id',
                $message->assignees->pluck('sub_team_id')
            ),

            default => AppUser::query()->whereRaw('1 = 0'),
        };
    }
}
