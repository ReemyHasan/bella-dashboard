<?php

namespace App\Http\Controllers\Mobile\V1\Products;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\CompetitionResource;
use App\Services\Mobile\CompetitionService;
use Illuminate\Http\Request;

class CompetitionController extends Controller
{
    public function __construct(private CompetitionService $competitionService) {}

    public function index(Request $request)
    {
        $competitions = $this->competitionService->list($request);
        return response()->format($this->returnPaginatedResponse($competitions, CompetitionResource::collection($competitions)), 'messages.success', 200);
    }

    public function show($id)
    {
        $competition = $this->competitionService->show($id);
        return response()->format(new CompetitionResource($competition), 'messages.success', 200);
    }
}
