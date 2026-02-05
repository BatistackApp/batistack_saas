<?php

use App\Http\Controllers\Banque\BankAccountController;
use App\Http\Controllers\Banque\BankTransactionController;
use App\Http\Controllers\Banque\ReconciliationController;
use App\Http\Controllers\Fleet\VehicleAssignmentController;
use App\Http\Controllers\Fleet\VehicleConsumptionController;
use App\Http\Controllers\Fleet\VehicleController;
use App\Http\Controllers\Fleet\VehicleInspectionController;
use App\Http\Controllers\GED\DocumentController;
use App\Http\Controllers\HR\EmployeeController;
use App\Http\Controllers\HR\TimeEntryController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/stripe/webhook', \App\Http\Controllers\Api\StripeWebhookController::class)
    ->name('stripe.webhook')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

Route::prefix('articles')->group(function () {
    Route::prefix('article')->group(function () {
        Route::get('/', [\App\Http\Controllers\Articles\ArticleController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Articles\ArticleController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'show']);
        Route::put('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\Articles\ArticleController::class, 'destroy']);
    });

    Route::prefix('ouvrage')->group(function () {
        Route::get('/', [\App\Http\Controllers\Articles\OuvrageController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Articles\OuvrageController::class, 'store']);
        Route::get('{id}', [\App\Http\Controllers\Articles\OuvrageController::class, 'show']);
        Route::post('{id}/consume', [\App\Http\Controllers\Articles\OuvrageController::class, 'consume']);
        Route::put('{id}', [\App\Http\Controllers\Articles\OuvrageController::class, 'update']);
        Route::delete('{id}', [\App\Http\Controllers\Articles\OuvrageController::class, 'destroy']);
    });

    Route::prefix('inventory')->group(function () {
        Route::post('/sessions', [\App\Http\Controllers\Articles\InventorySessionController::class, 'store']);
        Route::post('/sessions/{id}/count', [\App\Http\Controllers\Articles\InventorySessionController::class, 'recordCount']);
    });

    Route::prefix('stock')->group(function () {
        Route::post('/movements', [\App\Http\Controllers\Articles\StockMovementController::class, 'store']);
    });
});

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

Route::prefix('fleet')->group(function () {
    Route::apiResource('vehicles', VehicleController::class);
    Route::post('vehicles/{vehicle}/sync', [VehicleController::class, 'syncApi'])
        ->name('vehicles.sync-api');

    // Affectations et Mouvements
    Route::post('assignments', [VehicleAssignmentController::class, 'store'])
        ->name('assignments.store');
    Route::patch('assignments/{assignment}/release', [VehicleAssignmentController::class, 'release'])
        ->name('assignments.release');

    // Conformité et Maintenance
    Route::post('inspections', [VehicleInspectionController::class, 'store'])
        ->name('inspections.store');

    // Consommations et Relevés
    Route::post('consumptions', [VehicleConsumptionController::class, 'store'])
        ->name('consumptions.store');
});

Route::prefix('hr')->group(function () {
    // Routes pour les employés
    Route::apiResource('employees', EmployeeController::class);

    // Routes pour les pointages
    Route::apiResource('time-entries', TimeEntryController::class);

    // Route spécifique pour la vérification des temps (Approbation)
    Route::patch('time-entries/{timeEntry}/verify', [TimeEntryController::class, 'verify'])
        ->name('time-entries.verify');

    // Route pour récupérer les pointages d'un employé spécifique (optionnel mais utile)
    Route::get('employees/{employee}/time-entries', function (\App\Models\HR\Employee $employee) {
        return response()->json($employee->timeEntries()->latest()->get());
    })->name('employees.time-entries');
});

Route::prefix('ged')->group(function () {
    // Explorateur et recherche
    Route::get('/', [DocumentController::class, 'index']);

    // Documents
    Route::post('/upload', [DocumentController::class, 'store']);
    Route::get('/download/{document}', [DocumentController::class, 'download']);
    Route::delete('/documents/{document}', [DocumentController::class, 'destroy']);

    // Dossiers
    Route::post('/folders', [DocumentController::class, 'storeFolder']);

    // Actions de masse
    Route::post('/bulk', [DocumentController::class, 'bulk']);
});

// Gestion des comptes bancaires
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
