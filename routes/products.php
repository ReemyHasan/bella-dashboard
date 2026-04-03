<?php

use App\Http\Controllers\Web\V1\Orders\OrderController;
use App\Http\Controllers\Web\V1\Products\OfferController;
use App\Http\Controllers\Web\V1\Products\MainCategoryController;
use App\Http\Controllers\Web\V1\Products\ProductController;
use App\Http\Controllers\Web\V1\Products\SubCategoryController;
use Illuminate\Support\Facades\Route;



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
Route::apiResource('customer-orders', OrderController::class);


Route::get('select-marketer-info/{marketerId}', [OrderController::class, 'selectMarketerInfo']);
Route::get('select-address-info/{addressId}', [OrderController::class, 'selectAddressInfo']);

Route::get('select-zone-products/{zoneId}', [OrderController::class, 'selectZoneProducts']);
Route::get('select-zone-offers/{zoneId}', [OrderController::class, 'selectZoneOffers']);

Route::get('select-warehouse-products/{warehouseId}', [OrderController::class, 'warehouseProducts']);
Route::get('select-warehouse-offers/{warehouseId}', [OrderController::class, 'warehouseOffers']);

Route::get('select-customer-addresses/{customerId}', [OrderController::class, 'customerAddresses']);

// Route::get('select-warehouse-keeper/{warehouseId}', [OrderController::class, 'selectWarehouseInfo']);
