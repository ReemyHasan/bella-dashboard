<?php

use App\Http\Controllers\Shared\V1\SharedSelectController;
use Illuminate\Support\Facades\Route;



Route::middleware(['auth:sanctum', 'api.blocked'])->group(function () {

    Route::get('select-my-marketer-info', [SharedSelectController::class, 'selectMarketerInfo']);

    Route::get('select-marketer-info/{marketerId}', [SharedSelectController::class, 'selectMarketerInfo']);
    Route::get('select-address-info/{addressId}', [SharedSelectController::class, 'selectAddressInfo']);

    Route::get('select-zone-products/{zoneId}', [SharedSelectController::class, 'selectZoneProducts']);
    Route::get('select-zone-offers/{zoneId}', [SharedSelectController::class, 'selectZoneOffers']);

    Route::get('select-warehouse-products/{warehouseId}', [SharedSelectController::class, 'warehouseProducts']);
    Route::get('select-warehouse-offers/{warehouseId}', [SharedSelectController::class, 'warehouseOffers']);

    Route::get('select-customer-addresses/{customerId}', [SharedSelectController::class, 'customerAddresses']);
});
