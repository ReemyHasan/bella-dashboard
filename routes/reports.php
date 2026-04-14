<?php

use App\Http\Controllers\Web\V1\Reports\FinancialReportController;
use App\Http\Controllers\Web\V1\Reports\OrderReportsController;
use App\Http\Controllers\Web\V1\Reports\ProductReportsController;
use App\Http\Controllers\Web\V1\Reports\SalesReportController;
use App\Http\Controllers\Web\V1\Reports\TeamsReportController;
use App\Http\Controllers\Web\V1\Reports\VaultReportsController;
use App\Http\Controllers\Web\V1\Reports\WarehouseReportController;
use App\Http\Controllers\Web\V1\Reports\UserReportsController;

use Illuminate\Support\Facades\Route;

Route::get('reports/cash-requests', [FinancialReportController::class, 'cashRequestReport']);

Route::get('reports/sold-stagnant-products', [ProductReportsController::class, 'soldAndStagnantProductsReport']);

Route::get('reports/sales', [SalesReportController::class, 'salesReport']);
Route::get('reports/marketers-orders', [SalesReportController::class, 'subTeamMarketersReport']);
Route::get('reports/marketers-daily', [SalesReportController::class, 'marketerDailyReport']);
Route::get('reports/marketer-one-day', [SalesReportController::class, 'marketerOrdersReport']);

Route::get('reports/product-zone-prices', [ProductReportsController::class, 'productZoneReport']);

Route::get('reports/warehouses', [WarehouseReportController::class, 'warehouseReport']);
Route::get('reports/teams', [TeamsReportController::class, 'teamsHierarchyReport']);
Route::get('reports/orders', [OrderReportsController::class, 'ordersReport']);
Route::get('reports/warehouse-orders', [OrderReportsController::class, 'ordersWarehouseManReport']);
Route::get('reports/orders-items', [OrderReportsController::class, 'itemsReport']);

Route::get('reports/vaults-summary', [VaultReportsController::class, 'vaultReport']);
Route::get('reports/vault-details', [VaultReportsController::class, 'vaultDetails']);

Route::get('reports/user-balance-details', [UserReportsController::class, 'userBalanceLedgerReport']);

