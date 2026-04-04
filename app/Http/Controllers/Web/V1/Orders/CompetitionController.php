<?php

namespace App\Http\Controllers\Web\V1\Orders;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Orders\CompetitionRequest;
use App\Http\Resources\DashUser\Orders\CompetitionParticipantResource;
use App\Http\Resources\DashUser\Orders\CompetitionResource;
use App\Models\Competition;
use App\Services\DashUser\Orders\CompetitionService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CompetitionController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_competitions', only: ['index']),
            new Middleware('permission:view_competition_by_id', only: ['show']),
            new Middleware('permission:create_competition', only: ['store']),
            new Middleware('permission:update_competition', only: ['update']),
            new Middleware('permission:delete_competition', only: ['destroy']),
            new Middleware('permission:change_status_competition', only: ['activate']),
        ];
    }

    public function __construct(private CompetitionService $competitionService) {}

    public function index(Request $request)
    {
        $competitions = $this->competitionService->list($request);
        return response()->format($this->returnPaginatedResponse($competitions, CompetitionResource::collection($competitions)), 'messages.success', 200);
    }

    public function leaderboard(Request $request, Competition $competition)
    {
        $competitions = $this->competitionService->leaderboard($competition, $request);
        return response()->format($this->returnPaginatedResponse($competitions, CompetitionParticipantResource::collection($competitions)), 'messages.success', 200);
    }

    public function store(CompetitionRequest $request)
    {
        $competition = $this->competitionService->create($request->validated());
        return response()->format(new CompetitionResource($competition),  __('messages.created_successfully',  ['item' => __('constants.competition')]), 201);
    }

    public function update(CompetitionRequest $request, Competition $competition)
    {
        $competition = $this->competitionService->update($competition, $request->validated());
        return response()->format(new CompetitionResource($competition),  __('messages.updated_successfully',  ['item' => __('constants.competition')]), 200);
    }
    public function show(Competition $competition)
    {
        $competition = $this->competitionService->show($competition);
        return response()->format(new CompetitionResource($competition), 'messages.success', 200);
    }
    public function destroy(Competition $competition)
    {
        $returned = $this->competitionService->delete($competition);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.competition')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.competition')]), 200);
    }

    public function activate(Competition $competition)
    {

        $competition = $this->competitionService->activate(
            $competition
        );

        return response()->format(
            new CompetitionResource($competition),
            __('messages.handled_successfully', ['item' => __('constants.competition')]),
            200
        );
    }

    public function selectAvailable(Request $request)
    {
        $status = $request->input('status');


        $competitions = $this->competitionService->selectAvailable(
            $status,
        );

        $returnedData = $competitions->map(fn($competition) => [
            'key' => $competition?->id,
            'value' => $competition?->name

        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
