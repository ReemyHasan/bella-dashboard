<?php

namespace App\Http\Controllers\Web\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Auth\ChangePasswordRequest;
use Illuminate\Support\Facades\Hash;

class PasswordResetController extends Controller
{
      public function changePassword(ChangePasswordRequest $request)
    {
        $client = auth()->user();
        $currentPassword = $request->current_password;
        $newPassword = $request->new_password;

        if (!Hash::check($currentPassword, $client->password)) {

            return response()->format(null, __('messages.password_incorrect'), 400);
        }

        $client->password = Hash::make($newPassword);
        $client->save();

    
        return response()->format(null, __('messages.password_reset_successfully'), 200);
    }
}
