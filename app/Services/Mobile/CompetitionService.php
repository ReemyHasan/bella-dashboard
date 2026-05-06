<?php

namespace App\Services\Mobile;

use App\Enums\CompetitionStatus;
use App\Enums\PaginationEnum;
use App\Models\AppUser;
use App\Models\Competition;

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
            ->where('status', CompetitionStatus::active->value)
            ->where(function ($q) use ($user) {

                // ✅ 1. ALL competitions (everyone sees)
                $q->where('target', 'all');

                // ✅ 2. marketer participation
                $q->orWhereHas('marketers', function ($sub) use ($user) {
                    $sub->where('app_user_id', $user->id);
                });

                // 🔥 Team Manager
                if ($user->hasRole('Team Manager')) {

                    $teamId = $user->team_id;

                    // teams competitions
                    $q->orWhere(function ($sub) use ($teamId) {
                        $sub->where('target', 'teams')
                            ->whereHas(
                                'teams',
                                fn($t) =>
                                $t->where('teams.id', $teamId)
                            );
                    });

                    // subteams under his team
                    $q->orWhere(function ($sub) use ($teamId) {
                        $sub->where('target', 'subteams')
                            ->whereHas(
                                'subteams',
                                fn($st) =>
                                $st->where('team_id', $teamId)
                            );
                    });
                }

                // 🔥 Team Leader
                elseif ($user->hasRole('Team Leader')) {

                    $subteamId = $user->subteam_id;

                    $q->orWhere(function ($sub) use ($subteamId) {
                        $sub->where('target', 'subteams')
                            ->whereHas(
                                'subteams',
                                fn($st) =>
                                $st->where('subteams.id', $subteamId)
                            );
                    });
                }
            });

        $competitions = $query
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);

        $competitions->getCollection()->transform(function ($competition) use ($user) {

            $participants = $competition->participants;

            $participant = $participants->first(function ($p) use ($user, $competition) {

                return match ($competition->target) {

                    'all',
                    'marketers'
                    => $p->participant_id == $user->id
                        && $p->participant_type === AppUser::class,

                    'teams'
                    => $p->participant_id == $user->team_id,

                    'subteams'
                    => $p->participant_id == $user->subteam_id,

                    default => false,
                };
            });

            $rank = null;

            if ($participant) {
                $rank = $participants
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

        $competition = Competition::query()
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
            ->where('status', CompetitionStatus::active->value)

            ->where(function ($q) use ($user) {

                $q->where('target', 'all');

                $q->orWhereHas(
                    'marketers',
                    fn($m) =>
                    $m->where('app_user_id', $user->id)
                );

                if ($user->hasRole('Team Manager')) {

                    $q->orWhereHas(
                        'teams',
                        fn($t) =>
                        $t->where('teams.id', $user->team_id)
                    );

                    $q->orWhereHas(
                        'subteams',
                        fn($st) =>
                        $st->where('team_id', $user->team_id)
                    );
                } elseif ($user->hasRole('Team Leader')) {

                    $q->orWhereHas(
                        'subteams',
                        fn($st) =>
                        $st->where('subteams.id', $user->subteam_id)
                    );
                }
            })

            ->findOrFail($id);
        $participants = $competition->participants;

        $participant = $participants->first(function ($p) use ($user, $competition) {

            return match ($competition->target) {

                'all',
                'marketers'
                => $p->participant_id == $user->id
                    && $p->participant_type === AppUser::class,

                'teams'
                => $p->participant_id == $user->team_id,

                'subteams'
                => $p->participant_id == $user->subteam_id,

                default => false,
            };
        });

        $rank = null;

        if ($participant) {
            $rank = $participants
                ->pluck('id')
                ->search($participant->id) + 1;
        }

        $competition->my_rank = $rank;
        $competition->my_score = $participant?->score ?? 0;

        return $competition;
    }
}
