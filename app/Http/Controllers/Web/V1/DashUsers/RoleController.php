<?php

namespace App\Http\Controllers\Web\V1\DashUsers;

use App\Http\Controllers\Controller;
use App\Http\Requests\DashUser\Users\RoleRequest;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Resources\DashUser\RoleResource;
use App\Services\DashUser\RoleService;

class RoleController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission:view_all_roles', only: ['index']),
            new Middleware('permission:view_role_by_id', only: ['show']),
            new Middleware('permission:create_role', only: ['store']),
            new Middleware('permission:update_role', only: ['update']),
            new Middleware('permission:delete_role', only: ['destroy']),

        ];
    }

    public function __construct(private RoleService $roleService) {}

    public function index(Request $request)
    {
        $roles = $this->roleService->list($request);
        return response()->format($this->returnPaginatedResponse($roles, RoleResource::collection($roles)), 'messages.success', 200);
    }

    public function store(RoleRequest $request)
    {
        $role = $this->roleService->create($request->validated());
        return response()->format(new RoleResource($role),  __('messages.created_successfully',  ['item' => __('constants.role')]), 201);
    }

    public function update(RoleRequest $request, Role $role)
    {
        $role = $this->roleService->update($role, $request->validated());
        return response()->format(new RoleResource($role),  __('messages.updated_successfully',  ['item' => __('constants.role')]), 200);
    }
    public function show(Role $role)
    {
        $role = $this->roleService->show($role);
        return response()->format(new RoleResource($role), 'messages.success', 200);
    }
    public function destroy(Role $role)
    {
        $returned = $this->roleService->delete($role);
        if (!$returned) {
            return response()->format(null,  __('messages.deletion_prohibited',  ['item' => __('constants.role')]), 403);
        }
        return response()->format(null,  __('messages.deleted_successfully',  ['item' => __('constants.role')]), 200);
    }

    public function availablePermissions(string $type)
    {
        $permissions = $this->roleService->availablePermissions($type);

        return response()->format($permissions, 'messages.success', 200);
    }

    public function availableRolesByType(string $type)
    {
        $roles = $this->roleService->availableRolesByType($type);

        $returnedData = $roles->map(fn($role) => [
            'key' => $role?->id,
            'value' => $role?->name
        ]);
        return response()->format($returnedData, 'messages.success', 200);
    }
}
