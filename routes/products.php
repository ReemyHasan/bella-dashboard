<?php

use App\Http\Controllers\Web\V1\Orders\CompetitionController;
use App\Http\Controllers\Web\V1\Orders\OrderController;
use App\Http\Controllers\Web\V1\Products\BrandController;
use App\Http\Controllers\Web\V1\Products\OfferController;
use App\Http\Controllers\Web\V1\Products\MainCategoryController;
use App\Http\Controllers\Web\V1\Products\ProductController;
use App\Http\Controllers\Web\V1\Products\SubCategoryController;
use Illuminate\Support\Facades\Route;

Route::apiResource('brands', BrandController::class);

Route::get('select-brands', [BrandController::class, 'selectAvailable']);


Route::apiResource('main-categories', MainCategoryController::class);

Route::get('select-main-categories', [MainCategoryController::class, 'selectAvailable']);





Route::apiResource('sub-categories', SubCategoryController::class);

Route::get('select-sub-categories', [SubCategoryController::class, 'selectAvailable']);



Route::get('products/{product}/warehouses', [ProductController::class, 'productWarehouses']);

Route::post('products/{product}/images/sync', [ProductController::class, 'syncImages']);
Route::post('products/{product}/zones/sync', [ProductController::class, 'syncZonePrices']);

Route::apiResource('products', ProductController::class);

Route::get('select-products', [ProductController::class, 'selectAvailable']);

Route::post('apply-adjustment-products', [ProductController::class, 'applyAdjustment']);
Route::post('remove-adjustment-products', [ProductController::class, 'removeAdjustment']);




Route::post('offers/{offer}/images/sync', [OfferController::class, 'syncImages']);
Route::post('offers/{offer}/zones/sync', [OfferController::class, 'syncZonePrices']);

Route::apiResource('offers', OfferController::class);


Route::get('customer-orders/{customer_order}/transactions', [OrderController::class, 'transactions']);
Route::post('customer-orders/{customer_order}/handle', [OrderController::class, 'handle']);
// Route::get('customer-orders/{customer_order}/share-profits', [OrderController::class, 'handleFinancialProcess']);

Route::apiResource('customer-orders', OrderController::class);


// Route::get('select-warehouse-keeper/{warehouseId}', [OrderController::class, 'selectWarehouseInfo']);



Route::get('competitions/{competition}/activate', [CompetitionController::class, 'activate']);
Route::get('competitions/{competition}/leaderboard', [CompetitionController::class, 'leaderboard']);

Route::apiResource('competitions', CompetitionController::class);

Route::get('select-competitions', [CompetitionController::class, 'selectAvailable']);
