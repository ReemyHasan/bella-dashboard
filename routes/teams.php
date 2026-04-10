<?php

use App\Http\Controllers\Web\V1\Teams\AppUserController;
use App\Http\Controllers\Web\V1\Teams\SubTeamController;
use App\Http\Controllers\Web\V1\Teams\TeamController;
use Illuminate\Support\Facades\Route;


Route::post('teams/{team}/update-users', [TeamController::class, 'updateTeamUsers']);

Route::apiResource('teams', TeamController::class);
Route::get('select-teams', [TeamController::class, 'selectAvailable']);


Route::apiResource('sub-teams', SubTeamController::class);
Route::get('select-sub-teams', [SubTeamController::class, 'selectAvailable']);




Route::patch('app-users/{user}/password', [AppUserController::class, 'setPassword']);
Route::patch('app-users/{user}/status', [AppUserController::class, 'changeStatus']);
// Route::post('app-users/{user}/update-permissions', 'updatePermissions');

Route::get('marketer-balance/{id}', [AppUserController::class, 'marketerBalance']);

Route::apiResource('app-users', AppUserController::class);
Route::get('select-app-users', [AppUserController::class, 'selectAvailable']);
Route::get('inactive-marketers', [AppUserController::class, 'inactiveMarketers']);
