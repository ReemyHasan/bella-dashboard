<?php

namespace App\Http\Controllers\Mobile\V1\Auth;

use App\Enums\DashUserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Auth\LoginRequest;
use App\Http\Resources\Mobile\LoginResource;
use App\Models\AppUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {


        $appUser = AppUser::with(['roles', 'team', 'subTeam.team', 'addresses', 'warehouse'])->where('user_name', $request->user_name)->first();


        if (!$appUser || ! Hash::check($request->password, $appUser->password)) {

            return response()->format(null, 'messages.credentials_failure', 401, false);
        }

        if ($appUser->status == DashUserStatus::INACTIVE->value) {
            return response()->format(null, 'messages.inactive_account', 403, false);
        }

        if ($appUser->status == DashUserStatus::BANNED->value) {
            return response()->format(null, 'messages.banned', 403, false);
        }

        $token = $appUser->createToken('app_user_token', ['app-user'])->plainTextToken;

        return response()->format([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_data' => new LoginResource($appUser)
        ], 'messages.login_success_message', 200, true);
    }

    public function logout()
    {
        $currentToken = auth()->user()->currentAccessToken();
        if ($currentToken) {
            $currentToken->delete();
        }
        return response()->format(null, 'messages.logout_success', 200, true);
    }
}
