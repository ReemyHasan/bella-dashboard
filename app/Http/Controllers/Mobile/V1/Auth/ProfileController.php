<?php

namespace App\Http\Controllers\Mobile\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\Mobile\ProfileResource;

class ProfileController extends Controller
{
      public function myProfile()
    {
        $appUser = auth()->user();
        $appUser->load(['roles', 'team', 'subTeam.team', 'addresses', 'warehouse']);

        return response()->format(new ProfileResource($appUser), __('messages.success'), 200);
    }
}
