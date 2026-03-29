<?php

use App\Http\Controllers\Web\V1\Reports\OrderReportsController;
use App\Http\Controllers\Web\V1\Reports\SalesReportController;
use App\Http\Controllers\Web\V1\Reports\TeamsReportController;
use App\Http\Controllers\Web\V1\Reports\WarehouseReportController;
use Illuminate\Support\Facades\Route;


Route::get('reports/sales', [SalesReportController::class, 'salesReport']);
Route::get('reports/marketers-orders', [SalesReportController::class, 'subTeamMarketersReport']);
Route::get('reports/marketers-daily', [SalesReportController::class, 'marketerDailyReport']);

Route::get('reports/warehouses', [WarehouseReportController::class, 'warehouseReport']);
Route::get('reports/teams', [TeamsReportController::class, 'teamsHierarchyReport']);
Route::get('reports/orders', [OrderReportsController::class, 'ordersReport']);
Route::get('reports/warehouse-orders', [OrderReportsController::class, 'ordersWarehouseManReport']);
Route::get('reports/orders-items', [OrderReportsController::class, 'itemsReport']);
