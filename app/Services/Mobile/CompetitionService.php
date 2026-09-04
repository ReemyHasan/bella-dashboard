<?php

namespace App\Services\Mobile;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionTarget;
use App\Enums\PaginationEnum;
use App\Models\AppUser;
use App\Models\Competition;
use App\Models\SubTeam;
use App\Models\Team;

class CompetitionService
{
    public function list($request)
    {
        $user = auth()->user();

        $query = Competition::query()
            ->with([
                'teams',
                'subteams',
                'marketers',
                'participants' => function ($q) {
                    $q->orderByDesc('score');
                }
            ])
            ->where('status', CompetitionStatus::active->value);

        $this->applyVisibility($query, $user);

        $competitions = $query
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);

        $competitions->getCollection()->transform(function ($competition) use ($user) {

            $participant = $this->getMyParticipant($competition, $user);

            $rank = null;

            if ($participant) {
                $rank = $competition->participants
                    ->pluck('id')
                    ->search($participant->id) + 1;
            }

            $competition->my_rank = $rank;
            $competition->my_score = $participant?->score ?? 0;

            return $competition;
        });

        return $competitions;
    }
    public function show($id)
    {
        $user = auth()->user();

        $query = Competition::query()
            ->with([
                'zones',
                'teams',
                'subteams',
                'marketers',
                'products',
                'offers',
                'winners.winner',
                'participants' => fn($q) => $q->orderByDesc('score'),
            ])
            ->where('status', CompetitionStatus::active->value);

        $this->applyVisibility($query, $user);

        $competition = $query->findOrFail($id);

        $participant = $this->getMyParticipant($competition, $user);

        $rank = null;

        if ($participant) {
            $rank = $competition->participants
                ->pluck('id')
                ->search($participant->id) + 1;
        }

        $competition->my_rank = $rank;
        $competition->my_score = $participant?->score ?? 0;

        return $competition;
    }


    private function applyVisibility($query, $user): void
    {
        $query->where(function ($q) use ($user) {

            $q->where(
                'target',
                CompetitionTarget::all->value
            );

            $q->orWhere(function ($sub) use ($user) {

                $sub->where(
                    'target',
                    CompetitionTarget::marketers->value
                )
                    ->whereHas('marketers', function ($m) use ($user) {
                        $m->where('marketer_id', $user->id);
                    });
            });

            if ($user->hasRole('Team Manager')) {

                $teamId = $user->team_id;

                if ($teamId) {
                    $q->orWhere(function ($sub) use ($teamId) {

                        $sub->where(
                            'target',
                            CompetitionTarget::teams->value
                        )
                            ->whereHas('teams', function ($team) use ($teamId) {
                                $team->where('teams.id', $teamId);
                            });
                    });
                }

                $q->orWhere(
                    'target',
                    CompetitionTarget::all_teams->value
                );

                if ($teamId) {
                    $q->orWhere(function ($sub) use ($teamId) {

                        $sub->where(
                            'target',
                            CompetitionTarget::subteams->value
                        )
                            ->whereHas('subteams', function ($subteam) use ($teamId) {
                                $subteam->where('team_id', $teamId);
                            });
                    });
                }
                $q->orWhere(
                    'target',
                    CompetitionTarget::all_subteams->value
                );
            } elseif ($user->hasRole('Team Leader')) {

                $subteamId = $user->subteam_id;

                if ($subteamId) {
                    $q->orWhere(function ($sub) use ($subteamId) {

                        $sub->where(
                            'target',
                            CompetitionTarget::subteams->value
                        )
                            ->whereHas('subteams', function ($subteam) use ($subteamId) {
                                $subteam->where('sub_teams.id', $subteamId);
                            });
                    });
                }

                $q->orWhere(
                    'target',
                    CompetitionTarget::all_subteams->value
                );
            }
        });
    }

    private function getMyParticipant($competition, $user)
    {
        return $competition->participants->first(function ($participant) use ($user, $competition) {

            return match ($competition->target) {

                CompetitionTarget::all->value =>

                $participant->participant_id == $user->id
                    && $participant->participant_type === AppUser::class,

                CompetitionTarget::marketers->value =>

                $participant->participant_id == $user->id
                    && $participant->participant_type === AppUser::class,

                CompetitionTarget::teams->value =>

                $participant->participant_id == $user->team_id
                    && $participant->participant_type === Team::class,

                CompetitionTarget::all_teams->value =>

                $participant->participant_id == $user->team_id
                    && $participant->participant_type === Team::class,

                CompetitionTarget::subteams->value =>

                $participant->participant_id == $user->subteam_id
                    && $participant->participant_type === SubTeam::class,

                CompetitionTarget::all_subteams->value =>

                $participant->participant_id == $user->subteam_id
                    && $participant->participant_type === SubTeam::class,

                default => false,
            };
        });
    }
}
