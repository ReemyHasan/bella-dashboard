<?php

use App\Http\Controllers\Mobile\V1\AppUser\AppUserRequestController;
use App\Http\Controllers\Mobile\V1\AppUser\FinancialOverviewController;
use App\Http\Controllers\Mobile\V1\AppUser\ProfileController;
use App\Http\Controllers\Mobile\V1\AppUser\WarehouseManReviewController;
use App\Http\Controllers\Mobile\V1\Auth\AuthController;
use App\Http\Controllers\Mobile\V1\Auth\PasswordResetController;
use App\Http\Controllers\Mobile\V1\Customers\CustomerController;
use App\Http\Controllers\Mobile\V1\Products\CategoryController;
use App\Http\Controllers\Mobile\V1\Products\CompetitionController;
use App\Http\Controllers\Mobile\V1\Products\OrderController;
use App\Http\Controllers\Mobile\V1\Products\ProductController;
use App\Http\Controllers\Mobile\V1\Products\WarehouseController;
use Illuminate\Support\Facades\Route;


Route::prefix('v1/mobile')->middleware('api')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->name('login');
    });


    Route::middleware(['auth:sanctum', 'api.blocked', 'user.type:app'])->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::post('change-password', [PasswordResetController::class, 'changePassword']);
        });

        Route::get('my-profile', [ProfileController::class, 'myProfile']);

        Route::get('my-balance-details', [ProfileController::class, 'userBalanceLedgerReport']);

        Route::get('main-categories', [CategoryController::class, 'mainCategoriesList']);
        Route::get('sub-categories/{main_category}', [CategoryController::class, 'subCategoriesList']);
        Route::get('brands', [CategoryController::class, 'brandList']);

        Route::get('products', [ProductController::class, 'index']);
        Route::patch('products/{product}/mark-as-important', [ProductController::class, 'markAsImportant']);

        Route::get('products/{product}', [ProductController::class, 'show']);
        Route::get('select-products', [ProductController::class, 'selectAvailable']);


        Route::get('managed-customer-orders', [OrderController::class, 'managedOrders']);
        Route::post('customer-orders/{customer_order}/handle', [OrderController::class, 'handle']);
        Route::patch('customer-orders/{customer_order}/add-notes', [OrderController::class, 'addNotes']);

        Route::apiResource('customer-orders', OrderController::class)->only('index', 'store', 'update', 'show');

        Route::apiResource('customers', CustomerController::class)->only('index', 'store', 'update', 'show');

        Route::apiResource('marketer-requests', AppUserRequestController::class)->only('index', 'store', 'show');
        Route::apiResource('warehouse-man-reviews', WarehouseManReviewController::class)->only('index', 'store');

        Route::get('warehouses', [WarehouseController::class, 'index']);
        Route::get('warehouses-products', [WarehouseController::class, 'warehouseProducts']);
        Route::get('warehouses-offers', [WarehouseController::class, 'warehouseOffers']);

        Route::get('my-competitions', [CompetitionController::class, 'index']);
        Route::get('my-competitions/{id}', [CompetitionController::class, 'show']);

        ///// Financial Overview 
        Route::get('user-balance-summary', [FinancialOverviewController::class, 'userBalanceSummary']);
        Route::get('user-orders-trend-over-time', [FinancialOverviewController::class, 'basePriceOverTime']);
        Route::get('user-top-sold-products', [FinancialOverviewController::class, 'topProducts']);
        Route::get('user-top-customers', [FinancialOverviewController::class, 'topCustomers']);
    });
});
