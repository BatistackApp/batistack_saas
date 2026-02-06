<?php
Route::prefix('commerce')->group(function () {
    Route::prefix('invoices')->group(function () {
        Route::get('/', [\App\Http\Controllers\Commerce\InvoicesController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Commerce\InvoicesController::class, 'store']);
        Route::post('/create-progress', [\App\Http\Controllers\Commerce\InvoicesController::class, 'createProgress']);
        Route::get('/{id}', [\App\Http\Controllers\Commerce\InvoicesController::class, 'show']);
        Route::post('/{id}/validate', [\App\Http\Controllers\Commerce\InvoicesController::class, 'validateInvoice']);
    });

    Route::prefix('purchase')->group(function () {
        Route::get('/', [\App\Http\Controllers\Commerce\PurchaseOrderController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Commerce\PurchaseOrderController::class, 'store']);
        Route::post('/{id}/receive', [\App\Http\Controllers\Commerce\PurchaseOrderController::class, 'receive']);
        Route::get('/{id}', [\App\Http\Controllers\Commerce\PurchaseOrderController::class, 'show']);
    });
});
