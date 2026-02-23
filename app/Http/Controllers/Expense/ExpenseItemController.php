<?php

namespace App\Http\Controllers\Expense;

use App\Enums\Expense\ExpenseStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Expense\StoreExpenseItemRequest;
use App\Models\Expense\ExpenseItem;
use App\Services\Expense\ExpenseCalculationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ExpenseItemController extends Controller
{
    public function __construct(
        protected ExpenseCalculationService $calcService
    ) {}

    /**
     * Ajout d'une ligne de frais avec calcul automatique et upload.
     */
    public function store(StoreExpenseItemRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Calcul des montants financiers pour les frais standards
        if (!$request->boolean('is_mileage')) {
            $amounts = $this->calcService->calculateFromTtc(
                (float) $data['amount_ttc'],
                (float) $data['tax_rate']
            );
            $data = array_merge($data, $amounts);
        }

        // Gestion du stockage du justificatif
        if ($request->hasFile('receipt_path')) {
            $tenantId = auth()->user()->tenants_id;
            // Organisation des fichiers par tenant et par mois pour faciliter l'archivage
            $path = "tenants/{$tenantId}/expenses/" . now()->format('Y-m');
            $data['receipt_path'] = $request->file('receipt_path')->store($path, 'public');
        }

        $item = ExpenseItem::create($data);

        return response()->json($item->load('category', 'project', 'phase'), 201);
    }

    /**
     * Suppression d'une ligne (l'Observer gère le recalcul du total).
     */
    public function destroy(ExpenseItem $expenseItem): JsonResponse
    {
        // Sécurité : Vérifier si le rapport parent est modifiable
        if (!$expenseItem->report->isEditable()) {
            return response()->json(['error' => 'Action impossible : le rapport est verrouillé.'], 422);
        }

        // Nettoyage physique du fichier
        if ($expenseItem->receipt_path) {
            Storage::disk('public')->delete($expenseItem->receipt_path);
        }

        $expenseItem->delete();

        return response()->json(['message' => 'Ligne supprimée.']);
    }
}
