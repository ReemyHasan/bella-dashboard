<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\AppUserResource;
use App\Services\Mobile\ManagementService;
use Illuminate\Http\Request;

class ManagementController extends Controller
{

    public function __construct(private ManagementService $appUserService) {}

    public function appUsers(Request $request)
    {
        $users = $this->appUserService->appUsers($request);
        return response()->format($this->returnPaginatedResponse($users, AppUserResource::collection($users)), 'messages.success', 200);
    }

    public function showAppUser($id)
    {
        $appUser = $this->appUserService->showAppUser($id);
        return response()->format(new AppUserResource($appUser), 'messages.success', 200);
    }

    public function marketersSales(Request $request)
    {
        $data = $request->validate([
            'sub_team_id' => ['nullable', 'exists:sub_teams,id'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date']
        ]);
        $info = $this->appUserService->marketersSales($data);
        return response()->format($info, 'messages.success', 200);
    }

    public function subteamsSales(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date']
        ]);
        $info = $this->appUserService->subteamsSales($data);
        return response()->format($info, 'messages.success', 200);
    }
}
