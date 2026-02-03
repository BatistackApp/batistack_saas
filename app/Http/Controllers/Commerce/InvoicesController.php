<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\CreateProgressStatementRequest;
use App\Http\Requests\Commerce\InvoicesRequest;
use App\Models\Commerce\Invoices;
use App\Models\Commerce\Quote;
use App\Services\Commerce\InvoicingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    public function __construct(
        protected InvoicingService $invoicingService,
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        $query = Invoices::with(['customer', 'project', 'quote']);

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        return response()->json($query->latest()->paginate(15));
    }

    public function store(InvoicesRequest $request): JsonResponse
    {
        $invoice = Invoices::create($request->validated());

        if ($request->has('items')) {
            $invoice->items()->createMany($request->items);
        }

        return response()->json($invoice, 201);
    }

    /**
     * CRÉATION DE SITUATION (Spécificité BTP)
     * Utilise le service pour calculer l'avancement cumulé.
     */
    public function createProgress(CreateProgressStatementRequest $request): JsonResponse
    {
        try {
            $quote = Quote::findOrFail($request->quote_id);
            $invoice = $this->invoicingService->createProgressStatement(
                $quote,
                $request->situation_number,
                $request->progress_data
            );

            return response()->json($invoice, 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Invoices $invoice): JsonResponse
    {
        return response()->json($invoice->load(['items.quoteItem', 'customer', 'project']));
    }

    /**
     * Validation finale de la facture (génère le PDF et scelle le numéro).
     */
    public function validateInvoice(Invoices $invoice): JsonResponse
    {
        try {
            $validatedInvoice = $this->invoicingService->validateInvoice($invoice);

            return response()->json([
                'message' => 'Facture validée et scellée avec succès.',
                'reference' => $validatedInvoice->reference,
                'status' => $validatedInvoice->status->value
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
