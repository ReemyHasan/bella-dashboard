<?php

namespace App\Http\Controllers\Mobile\V1\AppUser;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\ProfileResource;
use App\Services\Mobile\UserAccountService;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function __construct(private UserAccountService $service) {}

    public function myProfile()
    {
        $appUser = auth()->user();
        $appUser->load(['roles', 'team', 'subTeam.team', 'addresses', 'warehouse']);

        return response()->format(new ProfileResource($appUser), __('messages.success'), 200);
    }

    public function userBalanceLedgerReport(Request $request)
    {
        $data = $request->validate([
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date'],
        ]);

        $result = $this->service->userBalanceLedger($data);

        return response()->format($result, __('messages.success'), 200);
    }
}
