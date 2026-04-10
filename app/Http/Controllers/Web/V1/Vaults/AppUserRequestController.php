<?php

namespace App\Http\Controllers\Web\V1\Vaults;

use App\Http\Controllers\Controller;
use App\Models\AppUserRequest;
use App\Http\Resources\DashUser\AppUserRequestResource;
use App\Services\DashUser\AppUserRequestService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AppUserRequestController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_user_requests', only: ['index']),
            new Middleware('permission:view_user_request_by_id', only: ['show']),

            new Middleware('permission:handle_user_request', only: ['handle']),
            new Middleware('permission:mark_as_read_user_request', only: ['markAsRead']),




        ];
    }

    public function __construct(private AppUserRequestService $appUserRequestService) {}

    public function index(Request $request)
    {
        $requests = $this->appUserRequestService->list($request);
        return response()->format($this->returnPaginatedResponse($requests, AppUserRequestResource::collection($requests)), 'messages.success', 200);
    }

    public function show(AppUserRequest $user_request)
    {
        $request = $this->appUserRequestService->show($user_request);
        return response()->format(new AppUserRequestResource($request), 'messages.success', 200);
    }
    public function markAsRead(AppUserRequest $request)
    {
        $this->appUserRequestService->review($request);

        return response()->format(null,  __('messages.reviewed_successfully',  ['item' => __('constants.app_user_request')]), 200);
    }

     public function handle(Request $request, AppUserRequest $app_user_request)
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'notes' => ['nullable', 'string']
        ]);

        $app_user_request = $this->appUserRequestService->handle(
            $app_user_request,
            $validated
        );

        return response()->format(
            new AppUserRequestResource($app_user_request),
            __('messages.handled_successfully', ['item' => __('constants.app_user_request')]),
            200
        );
    }
}
