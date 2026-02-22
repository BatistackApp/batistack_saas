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
        $amounts = [];

        // Gestion spécifique des frais kilométriques (IK)
        if ($request->boolean('is_mileage')) {
            $ht = $this->calcService->calculateMileage(
                auth()->user()->tenants_id,
                (float) $data['distance_km'],
                (int) $data['vehicle_power']
            );

            $amounts = [
                'amount_ht' => $ht,
                'amount_tva' => 0,
                'amount_ttc' => $ht,
                'tax_rate' => 0,
            ];
        } else {
            // Calcul standard TVA/HT depuis le TTC saisi
            $amounts = $this->calcService->calculateFromTtc(
                (float) $data['amount_ttc'],
                (float) $data['tax_rate']
            );
        }

        // Gestion du justificatif (Receipt)
        if ($request->hasFile('receipt_path')) {
            $tenantId = auth()->user()->tenants_id;
            $data['receipt_path'] = $request->file('receipt_path')->store(
                "tenants/{$tenantId}/expenses/receipts",
                'public'
            );
        }

        $item = ExpenseItem::create(array_merge($data, $amounts));

        return response()->json($item->load('category'), 201);
    }

    /**
     * Suppression d'une ligne (l'Observer gère le recalcul du total).
     */
    public function destroy(ExpenseItem $expenseItem): JsonResponse
    {
        // On vérifie que le rapport parent est modifiable
        if (! in_array($expenseItem->report->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected])) {
            return response()->json(['error' => 'Ligne verrouillée.'], 422);
        }

        if ($expenseItem->receipt_path) {
            Storage::disk('public')->delete($expenseItem->receipt_path);
        }

        $expenseItem->delete();

        return response()->json(['message' => 'Ligne supprimée.']);
    }
}
