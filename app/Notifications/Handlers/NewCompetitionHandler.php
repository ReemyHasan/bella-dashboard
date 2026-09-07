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
            });

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
            });
      
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
            'all_teams' =>
            AppUser::query()->whereIn(
                'team_id',
                $competition->teams->pluck('id')
            ),

            'all_subteams' =>
            AppUser::query()->whereIn(
                'subteam_id',
                $competition->subteams->pluck('id')
            ),
            default => AppUser::query()->whereRaw('1 = 0'),
        };
    }
}
