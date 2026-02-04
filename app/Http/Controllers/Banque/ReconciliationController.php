<?php

namespace App\Http\Controllers\Banque;

use App\Http\Controllers\Controller;
use App\Http\Requests\Banque\PaymentRequest;
use App\Models\Banque\BankTransaction;
use App\Models\Commerce\Invoices;
use App\Services\Banque\ReconciliationService;
use Illuminate\Http\JsonResponse;

class ReconciliationController extends Controller
{
    public function __construct(protected ReconciliationService $reconciliationService) {}

    /**
     * Valide le rapprochement entre une ligne de banque et une facture.
     */
    public function store(PaymentRequest $request): JsonResponse
    {
        $data = $request->validated();

        try {
            $transaction = BankTransaction::findOrFail($data['bank_transaction_id']);
            $invoice = Invoices::findOrFail($data['invoice_id']);

            $payment = $this->reconciliationService->reconcile(
                $transaction,
                $invoice,
                (float) $data['amount']
            );

            return response()->json([
                'message' => 'Rapprochement effectué avec succès.',
                'payment' => $payment,
                'invoice_status' => $invoice->refresh()->status->value,
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
