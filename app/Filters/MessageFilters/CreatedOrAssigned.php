<?php

namespace App\Filters\MessageFilters;

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

                // direct marketer assignment
                $q->where(function ($sub) use ($user) {

                    $sub->where('target_type', 'marketer')
                        ->whereHas('assignees', function ($a) use ($user) {
                            $a->where('marketer_id', $user->id);
                        });
                });

                // team assignment
                if ($user->team_id) {

                    $q->orWhere(function ($sub) use ($user) {

                        $sub->where('target_type', 'team')
                            ->whereHas('assignees', function ($a) use ($user) {
                                $a->where('team_id', $user->team_id);
                            });
                    });
                }

                // subteam assignment
                if ($user->subteam_id) {

                    $q->orWhere(function ($sub) use ($user) {

                        $sub->where('target_type', 'sub_team')
                            ->whereHas('assignees', function ($a) use ($user) {
                                $a->where('sub_team_id', $user->subteam_id);
                            });
                    });
                }
            });
        }
    }

    public function handleRange($value): void
    {
        // Not applicable for search, but you could add logic here if needed.
    }
}
