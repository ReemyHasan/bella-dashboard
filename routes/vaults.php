<?php

use App\Http\Controllers\Web\V1\Vaults\AppUserRequestController;
use App\Http\Controllers\Web\V1\Vaults\BalanceTransferRequestController;
use App\Http\Controllers\Web\V1\Vaults\PaymentMethodController;
use App\Http\Controllers\Web\V1\Vaults\UserRequestTypeController;
use App\Http\Controllers\Web\V1\Vaults\VaultController;
use App\Http\Controllers\Web\V1\Vaults\VaultTransferController;
use App\Http\Controllers\Web\V1\Vaults\CashRequestController;
use App\Http\Controllers\Web\V1\Vaults\FinancialAdjustmentController;
use Illuminate\Support\Facades\Route;



Route::get('vault-by-marketer/{id}', [VaultController::class, 'marketerVault']);

Route::patch('vaults/{vault}/update-vault-balance', [VaultController::class, 'updateUserBalance']);
Route::get('vaults/{vault}/transactions', [VaultController::class, 'transactions']);
Route::patch('vaults/update-company-balance', [VaultController::class, 'updateCompanyBalance']);
Route::apiResource('vaults', VaultController::class)->except('update');
Route::get('select-vaults', [VaultController::class, 'selectAvailable']);


Route::patch('vault-transfer/{vault_transfer}/confirm', [VaultTransferController::class, 'confirm']);
Route::patch('vault-transfer/{vault_transfer}/cancel', [VaultTransferController::class, 'cancel']);
Route::apiResource('vault-transfer', VaultTransferController::class);


Route::apiResource('user-request-types', UserRequestTypeController::class);
Route::get('select-user-request-types', [UserRequestTypeController::class, 'selectAvailable']);



Route::patch('user-requests/{request}/mark-as-read', [AppUserRequestController::class, 'markAsRead']);
Route::patch('user-requests/{app_user_request}/handle', [AppUserRequestController::class, 'handle']);

Route::apiResource('user-requests', AppUserRequestController::class)->only('index', 'show');

// Route::get('select-payment-methods', [PaymentMethodController::class, 'selectAvailable']);


Route::post('cash-requests/{cash_request}/handle', [CashRequestController::class, 'handle']);
Route::apiResource('cash-requests', CashRequestController::class);



Route::post('financial-adjustments/{financial_adjustment}/handle', [FinancialAdjustmentController::class, 'handle']);
Route::apiResource('financial-adjustments', FinancialAdjustmentController::class);




Route::post('balance-transfer-requests/{balance_transfer_request}/handle', [BalanceTransferRequestController::class, 'handle']);
Route::apiResource('balance-transfer-requests', BalanceTransferRequestController::class)->only('index', 'show');
