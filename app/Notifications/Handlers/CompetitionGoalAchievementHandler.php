<?php

namespace App\Notifications\Handlers;

use App\Events\NotificationEvent;
use App\Services\Notification\FirebaseNotificationService;
use App\Services\Notification\NotificationService;

class CompetitionGoalAchievementHandler
{
    public function __construct(
        protected NotificationService $notificationService,
        protected FirebaseNotificationService $firebaseNotificationService,
    ) {}
    public function handleDatabase(NotificationEvent $event): void
    {
        ['users' => $users, 'competition' => $competition]
            = $this->resolveData($event);

        foreach ($users as $user) {

            $this->notificationService->createNotification(
                type: $event->type->value,
                client: $user,
                title: $event->type->label(),
                body: "تم تحقيق هدف المسابقة {$competition->name}",
                data: [
                    'competition_id' => $competition->id,
                ]
            );
        }
    }

    public function handleFirebase(NotificationEvent $event): void
    {
        ['users' => $users, 'competition' => $competition]
            = $this->resolveData($event);

        foreach ($users as $user) {

            if (!$user->fcm_token) {
                continue;
            }

            $this->firebaseNotificationService->sendNotification(
                tokens: $user->fcm_token,
                title: $event->type->label(),
                body: "تم تحقيق هدف المسابقة {$competition->name}",
                data: [
                    'type' => $event->type->value,
                    'competition_id' => (string) $competition->id,
                ]
            );
        }
    }

    private function resolveData(NotificationEvent $event): array
    {
        $participant = $event->data['participant'];
        $competition = $event->data['competition'];

        $users = collect();

        // winner
        if ($participant->participant) {
            $users->push($participant->participant);
        }

        // manager/team leader
        $manager = match ($competition->target) {

            'teams'
            => optional($participant->participant)->team?->manager,

            'subteams'
            => optional($participant->participant)->subTeam?->teamLeader,

            default => null,
        };

        if ($manager) {
            $users->push($manager);
        }

        return [
            'users' => $users->unique('id'),
            'competition' => $competition,
        ];
    }
}
