<?php

namespace App\Http\Controllers\Web\V1\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Teams\TeamRequest;
use App\Http\Requests\DashUser\Teams\TeamUserRequest;
use App\Http\Resources\DashUser\TeamResource;
use App\Models\Team;
use App\Services\DashUser\TeamService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class TeamController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_teams', only: ['index']),
            new Middleware('permission:view_team_by_id', only: ['show']),
            new Middleware('permission:create_team', only: ['store']),
            new Middleware('permission:update_team', only: ['update', 'updateTeamUsers']),
            new Middleware('permission:delete_team', only: ['destroy']),

        ];
    }

    public function __construct(private TeamService $teamService) {}

    public function index(Request $request)
    {
        $teams = $this->teamService->list($request);
        return response()->format($this->returnPaginatedResponse($teams, TeamResource::collection($teams)), 'messages.success', 200);
    }

    public function store(TeamRequest $request)
    {
        $team = $this->teamService->create($request->validated());
        return response()->format(new TeamResource($team),  __('messages.created_successfully',  ['item' => __('constants.team')]), 201);
    }

    public function update(TeamRequest $request, Team $team)
    {
        $team = $this->teamService->update($team, $request->validated());
        return response()->format(new TeamResource($team),  __('messages.updated_successfully',  ['item' => __('constants.team')]), 200);
    }

    public function updateTeamUsers(TeamUserRequest $request, Team $team)
    {
        $team = $this->teamService->updateTeamUsers($team, $request->validated());
        return response()->format(new TeamResource($team),  __('messages.updated_successfully',  ['item' => __('constants.team_users')]), 200);
    }
    public function show(Team $team)
    {
        $team = $this->teamService->show($team);
        return response()->format(new TeamResource($team), 'messages.success', 200);
    }
    public function destroy(Team $team)
    {
        $returned = $this->teamService->delete($team);
        if (!$returned) {
            return response()->format(null, 'لا يمكنك حذف الفريق لديه فرق فرعية أو مستخدمين مرتبطين به.', 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.team')]), 200);
    }
    public function selectAvailable(Request $request)
    {
        $teams = $this->teamService->selectAvailable();

        $returnedData = $teams->map(fn($team) => [
            'key' => $team?->id,
            'value' => $team?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
