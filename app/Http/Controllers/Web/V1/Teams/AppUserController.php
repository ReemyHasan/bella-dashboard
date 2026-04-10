<?php

namespace App\Http\Controllers\Web\V1\Teams;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Teams\AppUserRequest;
use App\Http\Requests\DashUser\Users\UserPasswordRequest;
use App\Http\Resources\DashUser\AppUserResource;
use App\Models\AppUser;
use App\Services\DashUser\AppUserService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AppUserController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_app_users', only: ['index', 'inactiveMarketers']),
            new Middleware('permission:view_app_user_by_id', only: ['show']),
            new Middleware('permission:create_app_user', only: ['store']),
            new Middleware('permission:update_app_user', only: ['update', 'changeStatus', 'setPassword']),
            new Middleware('permission:delete_app_user', only: ['destroy']),

        ];
    }

    public function __construct(private AppUserService $appUserService) {}

    public function index(Request $request)
    {
        $appUsers = $this->appUserService->list($request);
        return response()->format($this->returnPaginatedResponse($appUsers, AppUserResource::collection($appUsers)), 'messages.success', 200);
    }

    public function inactiveMarketers(Request $request)
    {
        $appUsers = $this->appUserService->inactiveMarketers($request);
        return response()->format($this->returnPaginatedResponse($appUsers, AppUserResource::collection($appUsers)), 'messages.success', 200);
    }

    public function store(AppUserRequest $request)
    {
        $appUser = $this->appUserService->create($request->validated());
        return response()->format(new AppUserResource($appUser),  __('messages.created_successfully',  ['item' => __('constants.app_user')]), 201);
    }

    public function update(AppUserRequest $request, AppUser $appUser)
    {
        $appUser = $this->appUserService->update($appUser, $request->validated());
        return response()->format(new AppUserResource($appUser),  __('messages.updated_successfully',  ['item' => __('constants.app_user')]), 200);
    }
    public function show($id)
    {
        $appUser = $this->appUserService->show($id);
        return response()->format(new AppUserResource($appUser), 'messages.success', 200);
    }
    public function destroy(AppUser $appUser)
    {
        $returned = $this->appUserService->delete($appUser);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.app_user')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.app_user')]), 200);
    }
    public function selectAvailable(Request $request)
    {
        $team = $request->input('team');
        $subTeam = $request->input('subTeam');
        // $warehouse = $request->input('warehouse');

        $onlyUnassignedTeam = $request->input('onlyUnassignedTeam');
        $isWarehouseMan = $request->input('isWarehouseMan');
        $isTeamManager = $request->input('isTeamManager');
        $isSubTeamLeader = $request->input('isSubTeamLeader');


        $appUsers = $this->appUserService->selectAvailable(
            $team,
            $subTeam,
            $onlyUnassignedTeam,
            $isWarehouseMan,
            $isTeamManager,
            $isSubTeamLeader
        );

        $returnedData = $appUsers->map(fn($appUser) => [
            'key' => $appUser?->id,
            'value' => $appUser?->first_name . ' ' . $appUser?->last_name . ' (' . $appUser?->user_name . ')',

        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }


    public function setPassword(
        UserPasswordRequest $request,
        AppUser $user
    ) {
        $this->appUserService->updatePassword($user, $request->new_password);
        return response()->format(null, __('messages.password_reset_successfully',  ['item' => __('constants.app_user')]), 200);
    }

    public function changeStatus(Request $request, AppUser $user)
    {
        $validated = $request->validate([
            'action' => ['required', 'in:ban,unban,activate,deactivate']
        ]);

        $message = $this->appUserService->handleStatusChange($user, $validated['action']);

        return response()->format(null, $message, 200);
    }

    public function marketerBalance($id)
    {

        $returned = $this->appUserService->marketerBalance($id);

        return response()->format($returned, 'messages.success', 200);
    }

    // public function updatePermissions(UpdatePermissionsRequest $request, AppUser $user)
    // {

    //     $user = $this->appUserService->updatePermissions($user, $request->input('permissions'));

    //     return response()->format(new AppUserResource($user),  __('messages.updated_successfully',  ['item' => __('constants.app_user')]), 200);
    // }
}
