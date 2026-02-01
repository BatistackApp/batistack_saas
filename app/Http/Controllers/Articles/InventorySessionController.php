<?php

namespace App\Http\Controllers\Articles;

use App\Enums\Articles\InventorySessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\InventoryLineRequest;
use App\Http\Requests\Articles\InventorySessionRequest;
use App\Models\Articles\Article;
use App\Models\Articles\InventorySession;
use App\Models\Articles\Warehouse;
use App\Services\Articles\StockMovementService;
use Illuminate\Http\JsonResponse;

class InventorySessionController extends Controller
{
    public function __construct(
        protected StockMovementService $movementService
    ) {}

    public function index(): JsonResponse
    {
        $sessions = InventorySession::with(['warehouse', 'creator'])
            ->latest()
            ->paginate(15);

        return response()->json($sessions);
    }

    public function store(InventorySessionRequest $request): JsonResponse
    {
        $warehouse = Warehouse::findOrFail($request->warehouse_id);

        try {
            $session = $this->movementService->openInventorySession(
                $warehouse,
                $request->notes
            );

            return response()->json([
                'message' => "Session d'inventaire {$session->reference} ouverte pour le dépôt {$warehouse->name}.",
                'session' => $session
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function show(InventorySession $inventorySession): JsonResponse
    {
        return response()->json(
            $inventorySession->load(['warehouse', 'lines.article', 'creator', 'validator'])
        );
    }

    /**
     * Enregistrement d'un comptage (Saisie manuelle ou Scan).
     */
    public function recordCount(InventorySessionRequest $request, InventorySession $inventorySession): JsonResponse
    {
        try {
            // Résolution de l'article (soit par ID, soit par code scanné)
            $article = $request->article_id
                ? Article::findOrFail($request->article_id)
                : $this->movementService->resolveArticleByCode($request->scanned_code);

            $this->movementService->recordInventoryCount(
                $inventorySession,
                $article,
                (float) $request->counted_quantity
            );

            return response()->json([
                'message' => "Comptage enregistré pour {$article->name}.",
                'article_id' => $article->id,
                'status' => $inventorySession->refresh()->status->getLabel()
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Clôture et Validation de la session.
     * Cette action applique les écarts de stock de manière irréversible.
     */
    public function validateSession(InventorySession $inventorySession): JsonResponse
    {
        try {
            // On vérifie si la session peut être validée (non validée et non annulée)
            if ($inventorySession->status === InventorySessionStatus::Validated) {
                throw new \Exception("Cette session a déjà été validée.");
            }

            // Exécution de la validation (Génération des ajustements)
            $this->movementService->validateInventorySession($inventorySession);

            return response()->json([
                'message' => "L'inventaire {$inventorySession->reference} a été validé. Les stocks ont été régularisés.",
                'status' => $inventorySession->refresh()->status->value
            ]);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    public function destroy(InventorySession $inventorySession): JsonResponse
    {
        if ($inventorySession->status === InventorySessionStatus::Validated) {
            return response()->json(['message' => "Impossible d'annuler une session déjà validée."], 422);
        }

        $inventorySession->update(['status' => InventorySessionStatus::Cancelled]);

        return response()->json([
            'message' => "Session d'inventaire annulée. Le dépôt est de nouveau disponible."
        ]);
    }
}
