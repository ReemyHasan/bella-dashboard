<?php

use App\Http\Controllers\Shared\V1\NotificationController;
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
    Route::get('select-subteams/{teamId}', [SharedSelectController::class, 'selectAvailableSubteams']);
    Route::get('select-competitions', [SharedSelectController::class, 'selectAvailableCompetitions']);

    Route::get('select-customers', [SharedSelectController::class, 'selectCustomers']);
    Route::get('select-request-types', [SharedSelectController::class, 'selectAvailableAppUserRequestTypes']);
    Route::get('select-warehouse-men', [SharedSelectController::class, 'selectAvailableWarehouseMen']);

    Route::get('select-warehouses', [SharedSelectController::class, 'selectAvailableWarehouses']);

    Route::get('select-zones', [SharedSelectController::class, 'selectAvailableZones']);
    Route::get('select-regions', [SharedSelectController::class, 'selectAvailableRegions']);
    Route::get('select-addresses', [SharedSelectController::class, 'selectAvailableAddresses']);
    Route::get('select-cities', [SharedSelectController::class, 'selectAvailableCities']);

    Route::get('select-payment-methods', [SharedSelectController::class, 'selectAvailablePaymentMethod']);
    Route::get('select-marketers', [SharedSelectController::class, 'selectAvailableAppUser']);
    Route::get('select-currencies', [SharedSelectController::class, 'selectAvailableCurrency']);



    ////////////////////
    Route::get('notifications/{id}/mark-as-read', [NotificationController::class, 'markAsRead']);
    Route::get('notifications', [NotificationController::class, 'index']);
    Route::post('update-fcm-token', [NotificationController::class, 'updateFcmToken']);
});
