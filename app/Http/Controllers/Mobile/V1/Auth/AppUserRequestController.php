<?php

namespace App\Http\Controllers\Mobile\V1\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mobile\AppUser\AppUserRequestRequest;
use App\Http\Resources\Mobile\AppUserRequestResource;
use App\Models\AppUserRequest;
use App\Services\Mobile\AppUserRequestService;
use Illuminate\Http\Request;

class AppUserRequestController extends Controller
{

    public function __construct(private AppUserRequestService $appUserRequestService) {}

    public function index(Request $request)
    {
        $requests = $this->appUserRequestService->list($request);
        return response()->format($this->returnPaginatedResponse($requests, AppUserRequestResource::collection($requests)), 'messages.success', 200);
    }

    public function show($id)
    {
        $request = $this->appUserRequestService->show($id);
        return response()->format(new AppUserRequestResource($request), 'messages.success', 200);
    }

    public function store(AppUserRequestRequest $request)
    {
        $customer = $this->appUserRequestService->create($request->validated());
        return response()->format(null,  __('messages.created_successfully',  ['item' => __('constants.app_user_request')]), 201);
    }
}
