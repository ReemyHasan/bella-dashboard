<?php

namespace App\Http\Controllers\Web\V1\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Teams\SubTeamRequest;
use App\Http\Resources\DashUser\SubTeamResource;
use App\Models\SubTeam;
use App\Services\DashUser\SubTeamService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class SubTeamController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_subteams', only: ['index']),
            new Middleware('permission:view_subteam_by_id', only: ['show']),
            new Middleware('permission:create_subteam', only: ['store']),
            new Middleware('permission:update_subteam', only: ['update']),
            new Middleware('permission:delete_subteam', only: ['destroy']),

        ];
    }

    public function __construct(private SubTeamService $subteamService) {}

    public function index(Request $request)
    {
        $subteams = $this->subteamService->list($request);
        return response()->format($this->returnPaginatedResponse($subteams, SubTeamResource::collection($subteams)), 'messages.success', 200);
    }

    public function store(SubTeamRequest $request)
    {
        $subteam = $this->subteamService->create($request->validated());
        return response()->format(new SubTeamResource($subteam),  __('messages.created_successfully',  ['item' => __('constants.sub_team')]), 201);
    }

    public function update(SubTeamRequest $request, $subteam)
    {
        $subteam = $this->subteamService->update($subteam, $request->validated());
        return response()->format(new SubTeamResource($subteam),  __('messages.updated_successfully',  ['item' => __('constants.sub_team')]), 200);
    }
    public function show($subteam)
    {
        $subteam = $this->subteamService->show($subteam);
        return response()->format(new SubTeamResource($subteam), 'messages.success', 200);
    }
    public function destroy($subteam)
    {
        $returned = $this->subteamService->delete($subteam);
        if (!$returned) {
            return response()->format(null, 'لا يمكنك حذف الفريق لديه مستخدمين مرتبطين به.', 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.sub_team')]), 200);
    }
    public function selectAvailable(Request $request)
    {
        $team = $request->input('team');
        $subteams = $this->subteamService->selectAvailable($team);

        $returnedData = $subteams->map(fn($subteam) => [
            'key' => $subteam?->id,
            'value' => $subteam?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
