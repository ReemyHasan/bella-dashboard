<?php

namespace App\Http\Controllers\Web\V1\Vaults;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Vaults\UserRequestTypeRequest;
use App\Http\Resources\DashUser\TagResource;
use App\Models\UserRequestType;
use App\Services\DashUser\UserRequestTypeService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserRequestTypeController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_user_request_types', only: ['index']),
            new Middleware('permission:view_user_request_type_by_id', only: ['show']),
            new Middleware('permission:create_user_request_type', only: ['store']),
            new Middleware('permission:update_user_request_type', only: ['update']),
            new Middleware('permission:delete_user_request_type', only: ['destroy']),

        ];
    }

    public function __construct(private UserRequestTypeService $userRequestTypeService) {}

    public function index(Request $request)
    {
        $userRequestTypes = $this->userRequestTypeService->list($request);
        return response()->format($this->returnPaginatedResponse($userRequestTypes, TagResource::collection($userRequestTypes)), 'messages.success', 200);
    }

    public function store(UserRequestTypeRequest $request)
    {
        $userRequestType = $this->userRequestTypeService->create($request->validated());
        return response()->format(new TagResource($userRequestType),  __('messages.created_successfully',  ['item' => __('constants.user_request_type')]), 201);
    }

    public function update(UserRequestTypeRequest $request, UserRequestType $userRequestType)
    {
        $userRequestType = $this->userRequestTypeService->update($userRequestType, $request->validated());
        return response()->format(new TagResource($userRequestType),  __('messages.updated_successfully',  ['item' => __('constants.user_request_type')]), 200);
    }
    public function show(UserRequestType $userRequestType)
    {
        $userRequestType = $this->userRequestTypeService->show($userRequestType);
        return response()->format(new TagResource($userRequestType), 'messages.success', 200);
    }
    public function destroy(UserRequestType $userRequestType)
    {
        $returned = $this->userRequestTypeService->delete($userRequestType);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.user_request_type')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.user_request_type')]), 200);
    }

    public function selectAvailable()
    {
        $userRequestTypes = $this->userRequestTypeService->selectAvailable();

        $returnedData = $userRequestTypes->map(fn($userRequestType) => [
            'key' => $userRequestType?->id,
            'value' => $userRequestType?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
