<?php

use App\Http\Controllers\Web\V1\DashUsers\RoleController;
use App\Http\Controllers\Web\V1\DashUsers\UserController;
use Illuminate\Support\Facades\Route;

Route::apiResource('roles', RoleController::class);
Route::get('permissions/{type}', [RoleController::class, 'availablePermissions']);
Route::get('roles-by-type/{type}', [RoleController::class, 'availableRolesByType']);

Route::get('select-dash-users', [UserController::class, 'selectAvailable']);

Route::prefix('dash-users')->controller(UserController::class)->group(function () {
    Route::get('/', 'index');
    Route::post('/', 'store');
    Route::get('{user}', 'show');
    Route::put('{user}', 'update');
    Route::delete('{user}', 'destroy');
    Route::patch('{user}/password', 'setPassword');
    Route::patch('{user}/status', 'changeStatus');
    Route::post('{user}/update-permissions', 'updatePermissions');

});
// Route::get('deleted-users-list', [UserController::class, 'deletedList']);
// Route::patch('restore-user/{user}', [UserController::class, 'restore']);

