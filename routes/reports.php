<?php

use App\Http\Controllers\Web\V1\Reports\SalesReportController;
use App\Http\Controllers\Web\V1\Reports\WarehouseReportController;
use Illuminate\Support\Facades\Route;


Route::get('reports/sales', [SalesReportController::class, 'salesReport']);

Route::get('reports/warehouses', [WarehouseReportController::class, 'warehouseReport']);

