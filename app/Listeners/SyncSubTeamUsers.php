<?php

namespace App\Listeners;

use App\Events\SubTeamsSynced;
use App\Models\AppUser;
use App\Models\SubTeam;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncSubTeamUsers
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(SubTeamsSynced $event): void
    {
        $team = $event->team;
        $subTeamsPayload = $event->subTeams;

        $ids = collect($subTeamsPayload)
            ->pluck('id')
            ->filter()
            ->toArray();

        $subTeams = SubTeam::whereIn('id', $ids)->get()->keyBy('id');

        foreach ($subTeamsPayload as $payload) {

            if (empty($payload['id'])) {
                continue;
            }

            $subTeam = $subTeams[$payload['id']] ?? null;

            if (!$subTeam) {
                continue;
            }

            // Assign team leader
            if (!empty($payload['team_leader_id'])) {
                AppUser::where('id', $payload['team_leader_id'])
                    ->update(['team_id' => $subTeam->team_id, 'subteam_id' => $subTeam->id]);
            }
        }
    }
}
