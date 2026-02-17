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

        // Cas 1 : Indemnités Kilométriques
        if ($request->boolean('is_mileage')) {
            // Ici on pourrait récupérer le taux du tenant, exemple fixe à 0.60€ pour l'exemple
            $rate = 0.60;
            $totalKm = $this->calcService->calculateKm($data['distance_km'], $rate);

            $amounts = [
                'amount_ttc' => $totalKm,
                'amount_ht'  => $totalKm, // Pas de TVA sur les IK en général
                'amount_tva' => 0,
                'tax_rate'   => 0,
                'metadata'   => array_merge($data['metadata'] ?? [], [
                    'distance_km'    => $data['distance_km'],
                    'vehicle_power'  => $data['vehicle_power'],
                    'start_location' => $data['start_location'] ?? null,
                    'end_location'   => $data['end_location'] ?? null,
                ])
            ];
        }
        // Cas 2 : Frais standard sur justificatif
        else {
            $calc = $this->calcService->calculateFromTtc($data['amount_ttc'], $data['tax_rate']);
            $amounts = [
                'amount_ttc' => $calc['amount_ttc'],
                'amount_ht'  => $calc['amount_ht'],
                'amount_tva' => $calc['amount_tva'],
                'tax_rate'   => $data['tax_rate'],
            ];
        }

        // Gestion du fichier
        if ($request->hasFile('receipt')) {
            $tenantId = auth()->user()->tenants_id;
            $path = $request->file('receipt')->store("tenants/{$tenantId}/expenses/receipts", 'public');
            $amounts['receipt_path'] = $path;
        }

        $item = ExpenseItem::create(array_merge($data, $amounts));

        return response()->json([
            'message' => 'Ligne de frais ajoutée.',
            'data'    => $item->load('category')
        ], 201);
    }

    /**
     * Suppression d'une ligne (l'Observer gère le recalcul du total).
     */
    public function destroy(ExpenseItem $expenseItem): JsonResponse
    {
        // On vérifie que le rapport parent est modifiable
        if (!in_array($expenseItem->report->status, [ExpenseStatus::Draft, ExpenseStatus::Rejected])) {
            return response()->json(['error' => 'Ligne verrouillée.'], 422);
        }

        if ($expenseItem->receipt_path) {
            Storage::disk('public')->delete($expenseItem->receipt_path);
        }

        $expenseItem->delete();

        return response()->json(['message' => 'Ligne supprimée.']);
    }
}
