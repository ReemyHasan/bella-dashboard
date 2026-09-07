<?php

namespace App\Filters\MessageFilters;

use App\Enums\TargetType;
use App\Filters\QueryFilter;
use App\Filters\FilterContract;
use App\Models\AppUser;
use App\Models\SubTeam;
use App\Models\Team;

class CreatedOrAssigned extends QueryFilter implements FilterContract
{
    public function handle($value): void
    {
        $user = auth()->user();

        /*
    |--------------------------------------------------------------------------
    | created_by_me
    |--------------------------------------------------------------------------
    */
        if ($value === 'created_by_me') {

            $this->query->where(function ($q) use ($user) {
                $q->where('created_by_type', get_class($user))
                    ->where('created_by_id', $user->id);
            });

            return;
        }

        /*
    |--------------------------------------------------------------------------
    | assigned_to_me
    |--------------------------------------------------------------------------
    */
        if ($value === 'assigned_to_me') {

            $this->query->where(function ($q) use ($user) {

                /*
            |--------------------------------------------------------------------------
            | Marketer
            |--------------------------------------------------------------------------
            */

                $q->where(function ($sub) use ($user) {

                    // ALL marketers
                    $sub->where(function ($all) {
                        $all->where('assignment_type', 'all')
                            ->where('target_type', TargetType::MARKETER->value);
                    })

                        // Specific marketer
                        ->orWhere(function ($specific) use ($user) {
                            $specific->where('assignment_type', 'specific')
                                ->where('target_type', TargetType::MARKETER->value)
                                ->whereHas('assignees', function ($a) use ($user) {
                                    $a->where('marketer_id', $user->id);
                                });
                        });
                });


                /*
            |--------------------------------------------------------------------------
            | Team
            |--------------------------------------------------------------------------
            */

                if ($user->team_id) {

                    $q->orWhere(function ($sub) use ($user) {

                        // ALL teams
                        $sub->where(function ($all) {
                            $all->where('assignment_type', 'all')
                                ->where('target_type', TargetType::TEAM->value);
                        })

                            // Specific team
                            ->orWhere(function ($specific) use ($user) {
                                $specific->where('assignment_type', 'specific')
                                    ->where('target_type', TargetType::TEAM->value)
                                    ->whereHas('assignees', function ($a) use ($user) {
                                        $a->where('team_id', $user->team_id);
                                    });
                            });
                    });
                }


                /*
            |--------------------------------------------------------------------------
            | Sub Team
            |--------------------------------------------------------------------------
            */

                if ($user->subteam_id) {

                    $q->orWhere(function ($sub) use ($user) {

                        // ALL subteams
                        $sub->where(function ($all) {
                            $all->where('assignment_type', 'all')
                                ->where('target_type', TargetType::SUB_TEAM->value);
                        })

                            // Specific subteam
                            ->orWhere(function ($specific) use ($user) {
                                $specific->where('assignment_type', 'specific')
                                    ->where('target_type', TargetType::SUB_TEAM->value)
                                    ->whereHas('assignees', function ($a) use ($user) {
                                        $a->where('sub_team_id', $user->subteam_id);
                                    });
                            });
                    });
                }


                /*
            |--------------------------------------------------------------------------
            | Warehouse Keeper
            |--------------------------------------------------------------------------
            */

                if ($user->is_warehouse_man) {

                    $q->orWhere(function ($sub) use ($user) {

                        // ALL warehouse keepers
                        $sub->where(function ($all) {
                            $all->where('assignment_type', 'all')
                                ->where(
                                    'target_type',
                                    TargetType::WAREHOUSE_KEEPER->value
                                );
                        })

                            // Specific warehouse keeper
                            ->orWhere(function ($specific) use ($user) {
                                $specific->where('assignment_type', 'specific')
                                    ->where(
                                        'target_type',
                                        TargetType::WAREHOUSE_KEEPER->value
                                    )
                                    ->whereHas('assignees', function ($a) use ($user) {
                                        $a->where('marketer_id', $user->id);
                                    });
                            });
                    });
                }
            });

            return;
        }
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
