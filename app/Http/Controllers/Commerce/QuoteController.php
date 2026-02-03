<?php

namespace App\Http\Controllers\Commerce;

use App\Http\Controllers\Controller;
use App\Http\Requests\Commerce\QuoteRequest;
use App\Models\Commerce\Quote;
use App\Services\Commerce\QuoteService;
use Illuminate\Http\JsonResponse;

class QuoteController extends Controller
{
    public function __construct(
        protected QuoteService $quoteService
    ) {}

    public function index(): JsonResponse
    {
        $quotes = Quote::with(['customer', 'project'])->latest()->paginate(15);

        return response()->json($quotes);
    }

    public function store(QuoteRequest $request): JsonResponse
    {
        $quote = Quote::create($request->validated());

        if ($request->has('items')) {
            $quote->items()->createMany($request->items);
        }

        return response()->json($quote, 201);
    }

    /**
     * ACCEPTER UN DEVIS
     * Déclenche potentiellement la création du projet ou le démarrage des travaux.
     */
    public function accept(Quote $quote): JsonResponse
    {
        try {
            $this->quoteService->acceptQuote($quote);

            return response()->json(['message' => 'Devis accepté. Le chantier peut démarrer.']);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }

    public function show(Quote $quote): JsonResponse
    {
        return response()->json($quote->load(['items.article', 'items.ouvrage', 'customer', 'project']));
    }

    /**
     * Dupliquer un devis (pratique pour les variantes de prix).
     */
    public function duplicate(Quote $quote): JsonResponse
    {
        try {
            $newQuote = $this->quoteService->duplicateQuote($quote);

            return response()->json([
                'message' => 'Devis dupliqué avec succès.',
                'quote' => $newQuote->load('items')
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
