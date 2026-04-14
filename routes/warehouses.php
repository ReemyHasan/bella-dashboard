<?php

use App\Http\Controllers\Web\V1\Warehouses\InvoiceController;
use App\Http\Controllers\Web\V1\Warehouses\WarehouseController;
use App\Http\Controllers\Web\V1\Warehouses\WarehouseHandoverController;
use Illuminate\Support\Facades\Route;

Route::put('warehouses/{warehouse}/products', [WarehouseController::class, 'updateProducts']);
Route::get('warehouses/{warehouse}/products', [WarehouseController::class, 'warehouseProducts']);

Route::apiResource('warehouses', WarehouseController::class);

Route::get('select-warehouses', [WarehouseController::class, 'selectAvailable']);


Route::get('invoices/{invoice}/confirm', [InvoiceController::class, 'confirmInvoice']);

Route::apiResource('invoices', InvoiceController::class);


// Route::get('select-invoices', [InvoiceController::class, 'selectAvailable']);
Route::post('warehouse-handovers/{warehouseHandover}/approve', [WarehouseHandoverController::class, 'approve']);

Route::post('warehouse-handovers/{warehouseHandover}/reject', [WarehouseHandoverController::class, 'reject']);
Route::patch('warehouse-handovers/{warehouseHandover}/ship', [WarehouseHandoverController::class, 'shipHandover']);
Route::patch('warehouse-handovers/{warehouseHandover}/complete', [WarehouseHandoverController::class, 'completeHandover']);

Route::apiResource('warehouse-handovers', WarehouseHandoverController::class);
