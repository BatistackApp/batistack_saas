<?php

use App\Http\Controllers\Banque\BankAccountController;
use App\Http\Controllers\Banque\BankTransactionController;
use App\Http\Controllers\Banque\ReconciliationController;

Route::prefix('bank')->group(function () {
    Route::apiResource('bank-accounts', BankAccountController::class);
    Route::post('bank-accounts/{bank_account}/sync', [BankAccountController::class, 'sync'])
        ->name('bank-accounts.sync');

    // Flux de transactions et matching
    Route::get('bank-transactions', [BankTransactionController::class, 'index'])
        ->name('bank-transactions.index');

    Route::get('bank-transactions/{bank_transaction}/matches', [BankTransactionController::class, 'getMatches'])
        ->name('bank-transactions.matches');

    // Acte de réconciliation (Lettrage)
    Route::post('reconciliation', [ReconciliationController::class, 'store'])
        ->name('reconciliation.store');
});
