<?php

namespace App\Services\DashUser\Orders;

use App\Enums\CompetitionStatus;
use App\Enums\CompetitionTarget;
use App\Enums\NotificationType;
use App\Enums\PaginationEnum;
use App\Events\NotificationEvent;
use App\Exceptions\CustomException;
use App\Models\AppUser;
use App\Models\Competition;
use App\Models\CompetitionParticipant;
use App\Models\SubTeam;
use App\Models\Team;
use Illuminate\Support\Facades\DB;

class CompetitionService
{
    public function list($request)
    {
        return Competition::with([
            'createdBy',
            'coCreatedBy',
            'zones',
        ])->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function leaderboard(Competition $competition, $request)
    {
        return CompetitionParticipant::with([
            'participant'
        ])
            ->where('competition_id', $competition->id)
            ->filterBy($request->all())
            ->sortBy($request->get('sort', ['created_at' => 'desc']))
            ->latest()
            ->paginate(PaginationEnum::GeneralPagination->value);
    }

    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $creator = auth()->user();

            $coCreator = !empty($data['co_created_by_id'])
                ? AppUser::find($data['co_created_by_id'])
                : null;

            $CompData = [
                'co_created_by_type' => $coCreator ? AppUser::class : null,
                'created_by_id' => $creator->id,
                'created_by_type' => get_class($creator),
                'status' => CompetitionStatus::draft->value,

            ];
            $competition = Competition::create(array_merge($CompData, $data));

            $competition->zones()->sync($data['zones']);

            if ($data['target'] === CompetitionTarget::all_teams->value) {

                $teamIds = Team::query()
                    ->pluck('id');

                $competition->teams()->sync($teamIds);
            }

            if ($data['target'] === CompetitionTarget::subteams->value) {

                $competition->subteams()->sync(
                    $data['subteams']
                );
            }

            if ($data['target'] === CompetitionTarget::all_subteams->value) {

                $subteamQuery = SubTeam::query();

                if ($coCreator) {

                    if ($coCreator->hasRole('Team Manager')) {

                        $subteamQuery->where(
                            'team_id',
                            $coCreator->team_id
                        );
                    }
                }

                $subteamIds = $subteamQuery->pluck('id');

                $competition->subteams()->sync($subteamIds);
            }


            if ($data['target'] === CompetitionTarget::marketers->value) {

                $competition->marketers()->sync(
                    $data['marketers']
                );
            }

            if ($data['target'] === CompetitionTarget::all->value) {

                $marketerQuery = AppUser::query();

                if ($coCreator) {

                    if ($coCreator->hasRole('Team Manager')) {

                        $marketerQuery->where(
                            'team_id',
                            $coCreator->team_id
                        );
                    } elseif ($coCreator->hasRole('Team Leader')) {

                        $marketerQuery->where(
                            'sub_team_id',
                            $coCreator->sub_team_id
                        );
                    }
                }

                $marketerIds = $marketerQuery->pluck('id');

                $competition->marketers()->sync($marketerIds);
            }


            if ($data['type'] === 'product_sales') {
                $competition->products()->sync(
                    collect($data['products'])->mapWithKeys(fn($item) => [
                        $item['product_id'] => ['target_quantity' => $item['target_quantity']]
                    ])
                );
            }

            if ($data['type'] === 'offer_sales') {
                $competition->offers()->sync(
                    collect($data['offers'])->mapWithKeys(fn($item) => [
                        $item['offer_id'] => ['target_quantity' => $item['target_quantity']]
                    ])
                );
            }

            $competition->load([
                'createdBy',
                'coCreatedBy',
                'zones',
            ]);

            return $competition;
        });
    }

    public function update(Competition $competition, array $data)
    {
        if ($competition->status != CompetitionStatus::draft->value) {
            throw new CustomException('لا يمكنك تعديل المسابقة, أنها حاليا منتهية او نشطة.');
        }
        return DB::transaction(function () use ($competition, $data) {
            $creator = auth()->user();

            $coCreator = !empty($data['co_created_by_id'])
                ? AppUser::find($data['co_created_by_id'])
                : null;
            $competition->update($data);

            $competition->zones()->sync($data['zones']);

            $competition->teams()->sync([]);
            $competition->subteams()->sync([]);
            $competition->marketers()->sync([]);

            if ($data['target'] === CompetitionTarget::all_teams->value) {

                $teamIds = Team::query()
                    ->pluck('id');

                $competition->teams()->sync($teamIds);
            }

            if ($data['target'] === CompetitionTarget::subteams->value) {

                $competition->subteams()->sync(
                    $data['subteams']
                );
            }

            if ($data['target'] === CompetitionTarget::all_subteams->value) {

                $subteamQuery = SubTeam::query();

                if ($coCreator) {

                    if ($coCreator->hasRole('Team Manager')) {

                        $subteamQuery->where(
                            'team_id',
                            $coCreator->team_id
                        );
                    }
                }

                $subteamIds = $subteamQuery->pluck('id');

                $competition->subteams()->sync($subteamIds);
            }


            if ($data['target'] === CompetitionTarget::marketers->value) {

                $competition->marketers()->sync(
                    $data['marketers']
                );
            }

            if ($data['target'] === CompetitionTarget::all->value) {

                $marketerQuery = AppUser::query();

                if ($coCreator) {

                    if ($coCreator->hasRole('Team Manager')) {

                        $marketerQuery->where(
                            'team_id',
                            $coCreator->team_id
                        );
                    } elseif ($coCreator->hasRole('Team Leader')) {

                        $marketerQuery->where(
                            'sub_team_id',
                            $coCreator->sub_team_id
                        );
                    }
                }

                $marketerIds = $marketerQuery->pluck('id');

                $competition->marketers()->sync($marketerIds);
            }


            if ($data['type'] === 'product_sales') {
                $competition->products()->sync(
                    collect($data['products'])->mapWithKeys(fn($item) => [
                        $item['product_id'] => ['target_quantity' => $item['target_quantity']]
                    ])
                );
            }

            if ($data['type'] === 'offer_sales') {
                $competition->offers()->sync(
                    collect($data['offers'])->mapWithKeys(fn($item) => [
                        $item['offer_id'] => ['target_quantity' => $item['target_quantity']]
                    ])
                );
            }

            $competition->load([
                'createdBy',
                'coCreatedBy',
                'zones',
            ]);

            return $competition;
        });
    }
    public function show(Competition $competition)
    {
        $competition->load(
            [
                'createdBy',
                'coCreatedBy',
                'zones',
                'teams',
                'subteams',
                'marketers.team',
                'marketers.subTeam',
                'marketers.roles',
                'products',
                'offers',
                'winners.winner.team',
                'winners.winner.subTeam',
                'winners.winner.roles'

            ]
        );
        return $competition;
    }

    public function delete(Competition $competition)
    {

        if (in_array($competition->status, [
            CompetitionStatus::active->value,
            CompetitionStatus::ended->value
        ])) {
            throw new CustomException('لا يمكنك حذف المسابقة, أنها حاليا منتهية او نشطة.');
        }

        return $competition->delete();
    }


    public function selectAvailable($marketerId = null, $status = CompetitionStatus::active->value)
    {
        $marketer = $marketerId == null ? null : AppUser::findOrFail($marketerId);

        return Competition::query()
            ->when(!is_null($status), function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->when(!is_null($marketer), function ($query) use ($marketer) {

                $query->where(function ($query) use ($marketer) {

                    $query->whereIn('target', [CompetitionTarget::all->value, CompetitionTarget::all_subteams->value, CompetitionTarget::all_teams->value]);

                    $query->orWhere(function ($q) use ($marketer) {
                        $q->where('target', CompetitionTarget::marketers->value)
                            ->whereHas('marketers', function ($q2) use ($marketer) {
                                $q2->where('marketer_id', $marketer->id);
                            });
                    });

                    $query->orWhere(function ($q) use ($marketer) {
                        $q->where('target', CompetitionTarget::teams->value)
                            ->whereHas('teams', function ($q2) use ($marketer) {
                                $q2->where('team_id', $marketer->team_id);
                            });
                    });

                    $query->orWhere(function ($q) use ($marketer) {
                        $q->where('target', CompetitionTarget::subteams->value)
                            ->whereHas('subteams', function ($q2) use ($marketer) {
                                $q2->where('sub_team_id', $marketer->sub_team_id);
                            });
                    });
                });
            })

            ->orderBy('id')
            ->get([
                'id',
                'name',
                'status'
            ]);
    }

    public function activate(Competition $competition)
    {


        if (in_array($competition->status, [
            CompetitionStatus::active->value,
            CompetitionStatus::ended->value
        ])) {
            throw new CustomException('لا يمكنك تفعيل المسابقة, أنها حاليا منتهية او نشطة.');
        }
        $competition->update([
            'status' => CompetitionStatus::active->value,
        ]);
        event(new NotificationEvent(
            type: NotificationType::NEW_COMPETITION,
            data: [
                'competition' => $competition,
            ]
        ));
        return $competition;
    }
}
