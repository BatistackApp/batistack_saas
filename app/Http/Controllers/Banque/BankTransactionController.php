<?php

namespace App\Http\Controllers\Banque;

use App\Http\Controllers\Controller;
use App\Models\Banque\BankTransaction;
use App\Services\Banque\ReconciliationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class BankTransactionController extends Controller
{
    public function __construct(protected ReconciliationService $reconciliationService) {}

    /**
     * Liste des transactions (filtrables par état de rapprochement).
     */
    public function index(Request $request): JsonResponse
    {
        $query = BankTransaction::with('account');

        if ($request->has('reconciled')) {
            $query->where('is_reconciled', $request->boolean('reconciled'));
        }

        return response()->json($query->latest('value_date')->paginate(50));
    }

    /**
     * Récupère les suggestions de factures pour une transaction spécifique.
     */
    public function getMatches(BankTransaction $bankTransaction): JsonResponse
    {
        if ($bankTransaction->is_reconciled) {
            return response()->json(['message' => 'Cette transaction est déjà rapprochée.'], 422);
        }

        $suggestions = $this->reconciliationService->suggestMatches($bankTransaction);

        return response()->json($suggestions);
    }
}
