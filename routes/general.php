<?php

use App\Http\Controllers\Web\V1\Customers\CustomerController;
use App\Http\Controllers\Web\V1\General\AddressController;
use App\Http\Controllers\Web\V1\General\ChartsController;
use App\Http\Controllers\Web\V1\General\CityController;
use App\Http\Controllers\Web\V1\General\CurrencyController;
use App\Http\Controllers\Web\V1\General\MessageController;
use App\Http\Controllers\Web\V1\General\RegionController;
use App\Http\Controllers\Web\V1\General\SystemController;
use App\Http\Controllers\Web\V1\General\TagController;
use App\Http\Controllers\Web\V1\General\ZoneController;
use Illuminate\Support\Facades\Route;


Route::get('charts/financial-summary', [ChartsController::class, 'financialSummaryReport']);

Route::get('charts/main', [ChartsController::class, 'main']);
Route::get('charts/top-10', [ChartsController::class, 'tables']);
Route::get('charts/line-bar-charts', [ChartsController::class, 'lineAndBarCharts']);

Route::get('/system/clear-cache', [SystemController::class, 'clearCache']);
Route::get('/system/settings', [SystemController::class, 'all']);
Route::post('/system/settings', [SystemController::class, 'update']);

Route::apiResource('currencies', CurrencyController::class);

Route::get('select-currencies', [CurrencyController::class, 'selectAvailable']);



Route::apiResource('zones', ZoneController::class);

Route::get('select-zones', [ZoneController::class, 'selectAvailable']);
Route::get('select-tips', [ZoneController::class, 'selectAvailableTips']);



Route::apiResource('cities', CityController::class);

Route::get('select-cities', [CityController::class, 'selectAvailable']);


Route::apiResource('tags', TagController::class);

Route::get('select-tags', [TagController::class, 'selectAvailable']);


Route::apiResource('regions', RegionController::class);

Route::get('select-regions', [RegionController::class, 'selectAvailable']);


Route::apiResource('addresses', AddressController::class);

Route::get('select-addresses', [AddressController::class, 'selectAvailable']);
Route::get('select-marketer-addresses/{marketerId}', [AddressController::class, 'marketerAddresses']);

Route::apiResource('messages', MessageController::class);

Route::patch('customers/{user}/status', [CustomerController::class, 'changeStatus']);
Route::apiResource('customers', CustomerController::class);
