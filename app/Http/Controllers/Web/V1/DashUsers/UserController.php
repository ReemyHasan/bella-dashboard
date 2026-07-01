<?php

namespace App\Http\Controllers\Web\V1\DashUsers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Users\UpdatePermissionsRequest;
use App\Http\Requests\DashUser\Users\UserPasswordRequest;
use App\Http\Requests\DashUser\Users\UserRequest;
use App\Http\Resources\DashUser\UserResource;
use App\Models\DashUser;
use App\Services\DashUser\UserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_dash_users', only: ['index']),
            new Middleware('permission:view_dash_user_by_id', only: ['show']),
            new Middleware('permission:create_dash_user', only: ['store']),
            new Middleware('permission:update_dash_user', only: ['update', 'changeStatus']),
            new Middleware('permission:delete_dash_user', only: ['destroy']),
            new Middleware('permission:set_dash_user_password', only: ['setPassword']),
            // new Middleware('permission:users.view_deleted', only: ['deletedList']),
            // new Middleware('permission:users.restore', only: ['restore']),
            new Middleware('permission:update_dash_user_permissions', only: ['updatePermissions']),


        ];
    }
    public function __construct(protected UserService $userService) {}

    public function index(Request $request)
    {
        $users = $this->userService->list($request);
        return response()->format($this->returnPaginatedResponse($users, UserResource::collection($users)), 'messages.success');
    }

    public function store(UserRequest $request)
    {
        $user = $this->userService->create($request->validated());
        return response()->format(new UserResource($user),  __('messages.created_successfully',  ['item' => __('constants.dash_user')]), 201);
    }

    public function show($id)
    {
        $user = $this->userService->show($id);
        return response()->format(new UserResource($user), 'messages.success');
    }

    public function update(UserRequest $request, DashUser $user)
    {
        $user = $this->userService->update($user, $request->validated());
        return response()->format(new UserResource($user),  __('messages.updated_successfully',  ['item' => __('constants.dash_user')]), 200);
    }

    public function destroy(DashUser $user)
    {
        $this->userService->delete($user);
        return response()->format(null, __('messages.deleted_successfully',  ['item' => __('constants.dash_user')]), 200);
    }

    public function setPassword(
        UserPasswordRequest $request,
        DashUser $user
    ) {
        $this->userService->updatePassword($user, $request->new_password);
        return response()->format(null, __('messages.password_reset_successfully',  ['item' => __('constants.dash_user')]), 200);
    }

    public function changeStatus(Request $request, DashUser $user)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:ban,unban,activate,deactivate']
        ]);

        $message = $this->userService->handleStatusChange($user, $validated['action']);

        return response()->format(null, $message, 200);
    }

    public function updatePermissions(UpdatePermissionsRequest $request, DashUser $user)
    {

        $user = $this->userService->updatePermissions($user, $request->input('permissions'));

        return response()->format(new UserResource($user),  __('messages.updated_successfully',  ['item' => __('constants.dash_user')]), 200);
    }

    public function deletedList(Request $request)
    {
        $users = $this->userService->list($request, true);
        return response()->format($this->returnPaginatedResponse($users, UserResource::collection($users)), 'messages.success', 200);
    }



    public function restore($id)
    {
        $user = DashUser::onlyTrashed()->findOrFail($id);

        $user->restore();

        return response()->format(new UserResource($user), __('messages.restored_successfully',  ['item' => __('constants.dash_user')]), 200);
    }


    public function selectAvailable(Request $request)
    {
        $role = $request->input('role');
        $users = $this->userService->selectAvailable(
            $role
        );

        $returnedData = $users->map(fn($user) => [
            'key' => $user?->id,
            'value' => $user?->first_name . ' ' . $user?->last_name . ' (' . $user?->user_name . ')',

        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }

    public function userBalance($id)
    {

        $returned = $this->userService->userBalance($id);

        return response()->format($returned, 'messages.success', 200);
    }
}
