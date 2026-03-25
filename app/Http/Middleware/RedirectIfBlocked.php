<?php

namespace App\Http\Middleware;

use App\Enums\AdminStatus;
use App\Enums\DashUserStatus;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd(auth()->user());
         if (auth()->user()->status == DashUserStatus::INACTIVE->value) {
            return response()->format(null, 'messages.inactive_account', 403, false);
        }

        if (auth()->user()->status === DashUserStatus::BANNED->value)
            return response()->format(null, 'messages.user_blocked', 403, false);


        return $next($request);
    }
}
