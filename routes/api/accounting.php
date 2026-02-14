<?php

// --- Plan Comptable ---
use App\Http\Controllers\Accounting\AccountingEntryController;
use App\Http\Controllers\Accounting\ChartOfAccountController;

Route::get('chart-of-accounts', [ChartOfAccountController::class, 'index']);
Route::get('chart-of-accounts/{ulid}/balance', [ChartOfAccountController::class, 'getBalance']);

// --- Écritures Comptables ---
Route::apiResource('accounting-entries', AccountingEntryController::class)->except(['update', 'destroy']);
Route::post('accounting-entries/{ulid}/validate', [AccountingEntryController::class, 'validateEntry']);

// --- Clôtures et Fiscalité ---
Route::post('finance/close-period/{year}/{month}', [\App\Http\Controllers\Accounting\FinancialOperationController::class, 'closeMonth']);
Route::post('finance/export-fec', [\App\Http\Controllers\Accounting\FinancialOperationController::class, 'requestFec']);
