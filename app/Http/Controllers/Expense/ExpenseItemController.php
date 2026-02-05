<?php

namespace App\Http\Controllers\Expense;

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

        // Calcul des montants HT/TVA
        $amounts = $this->calcService->calculateFromTtc($data['amount_ttc'], $data['tax_rate']);

        // Gestion du justificatif
        if ($request->hasFile('receipt_path')) {
            $fileName = $request->file('receipt_path')->getFilename();
            $data['receipt_path'] = $request->file('receipt_path')->store('receipts/' . auth()->id().'/'.$fileName, 'public');
        }

        $item = ExpenseItem::create(array_merge($data, $amounts));

        return response()->json($item, 201);
    }

    /**
     * Suppression d'une ligne (l'Observer gère le recalcul du total).
     */
    public function destroy(ExpenseItem $item): JsonResponse
    {
        if ($item->receipt_path) {
            Storage::disk('public')->delete($item->receipt_path);
        }

        $item->delete();

        return response()->json(null, 204);
    }
}
