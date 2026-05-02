<?php

use App\Http\Controllers\Shared\V1\SharedSelectController;
use App\Http\Controllers\Web\V1\Auth\AuthController;
use App\Http\Controllers\Web\V1\Auth\PasswordResetController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->middleware('api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
    });


    Route::middleware(['auth:sanctum', 'api.blocked', 'user.type:dash'])->group(function () {



        require __DIR__ . '/users.php';
        require __DIR__ . '/general.php';
        require __DIR__ . '/products.php';
        require __DIR__ . '/warehouses.php';
        require __DIR__ . '/teams.php';
        require __DIR__ . '/vaults.php';
        require __DIR__ . '/reports.php';


        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('change-password', [PasswordResetController::class, 'changePassword']);
        });
    });
    require __DIR__ . '/shared.php';
});

require __DIR__ . '/mobile.php';
