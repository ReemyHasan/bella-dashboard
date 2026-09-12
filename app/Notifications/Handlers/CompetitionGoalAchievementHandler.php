<?php

namespace App\Notifications\Handlers;

use App\Enums\CompetitionTarget;
use App\Events\NotificationEvent;
use App\Models\AppUser;
use App\Models\SubTeam;
use App\Models\Team;
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

    $participantModel = $participant->participant;

    if (!$participantModel) {
        return [
            'users' => collect(),
            'competition' => $competition,
        ];
    }

    /*
     * MARKETER
     *
     * Participant itself is the marketer.
     */
    if (get_class($participantModel) == AppUser::class) {
        $users->push($participantModel);
    }

    /*
     * TEAM
     *
     * Participant itself is the Team.
     * Notify the Team Manager.
     */
    if (get_class($participantModel) == Team::class) {
        if ($participantModel->manager) {
            $users->push($participantModel->manager);
        }
    }

    /*
     * SUBTEAM
     *
     * Participant itself is the SubTeam.
     * Notify the Team Leader.
     */
    if (get_class($participantModel) == SubTeam::class) {
        if ($participantModel->teamLeader) {
            $users->push($participantModel->teamLeader);
        }
    }

    /*
     * ALL
     *
     * For `all`, participants are marketers.
     *
     * The co-creator is:
     * - Team Manager
     * - Team Leader
     *
     * depending on who created the competition.
     */
    if ($competition->target === CompetitionTarget::all->value) {
        $coCreator = $this->resolveAllCoCreator($competition);

        if ($coCreator) {
            $users->push($coCreator);
        }
    }

    return [
        'users' => $users->unique('id')->values(),
        'competition' => $competition,
    ];
}

private function resolveAllCoCreator($competition)
{
    $competition->loadMissing('coCreatedBy');

    $coCreator = $competition->coCreatedBy;

    if (!$coCreator) {
        return null;
    }

    if (
        $coCreator->hasRole('Team Manager') ||
        $coCreator->hasRole('Team Leader')
    ) {
        return $coCreator;
    }

    return null;
}
}
