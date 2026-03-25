<?php

namespace App\Http\Controllers\Web\V1\Auth;

use App\Enums\DashUserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Auth\LoginRequest;
use App\Http\Resources\DashUser\LoginResource;
use App\Models\DashUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {


        $admin = DashUser::with('roles.permissions')->where('user_name', $request->user_name)->first();

        if (!$admin || ! Hash::check($request->password, $admin->password)) {

            return response()->format(null, 'messages.credentials_failure', 401, false);
        }

        if ($admin->status == DashUserStatus::INACTIVE->value) {
            return response()->format(null, 'messages.inactive_account', 403, false);
        }

        if ($admin->status == DashUserStatus::BANNED->value) {
            return response()->format(null, 'messages.banned', 403, false);
        }

        $token = $admin->createToken('auth_token')->plainTextToken;

        return response()->format([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user_data' => new LoginResource($admin)
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
