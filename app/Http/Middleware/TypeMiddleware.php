<?php

namespace App\Http\Middleware;

use App\Models\AppUser;
use App\Models\DashUser;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TypeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $type): Response
    {
        $user = auth()->user();

        if ($type === 'app' && ! $user instanceof AppUser) {
            return response()->format(null, __('messages.unauthorized_user'), 403, false);
        }

        if ($type === 'dash' && ! $user instanceof DashUser) {
            return response()->format(null, __('messages.unauthorized_user'), 403, false);
        }

        return $next($request);
    }
}
