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

        // Logique de bascule Kilométrage vs Standard
        if ($request->boolean('is_mileage')) {
            $ttc = $this->calcService->calculateMileage(
                auth()->user()->tenants_id,
                $data['distance_km'],
                $data['vehicle_power'] ?? 5
            );

            $amounts = [
                'amount_ttc' => $ttc,
                'amount_ht' => $ttc, // Pas de TVA sur les IK
                'amount_tva' => 0,
                'tax_rate' => 0,
            ];
        } else {
            $amounts = $this->calcService->calculateFromTtc($data['amount_ttc'], $data['tax_rate']);
        }

        if ($request->hasFile('receipt_path')) {
            $tenantId = auth()->user()->tenants_id;
            $data['receipt_path'] = $request->file('receipt_path')->store("tenants/{$tenantId}/expenses", 'public');
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
