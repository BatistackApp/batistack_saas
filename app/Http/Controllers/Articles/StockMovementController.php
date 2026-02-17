<?php

namespace App\Http\Controllers\Articles;

use App\Enums\Articles\StockMovementType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Articles\StockMovementRequest;
use App\Models\Articles\Article;
use App\Models\Articles\StockMovement;
use App\Models\Articles\Warehouse;
use App\Services\Articles\StockMovementService;
use Illuminate\Http\JsonResponse;

class StockMovementController extends Controller
{
    public function __construct(protected StockMovementService $movementService) {}

    public function index(): JsonResponse
    {
        $movements = StockMovement::with(['article', 'warehouse', 'project', 'user'])
            ->latest()
            ->paginate(20);

        return response()->json($movements);
    }

    public function store(StockMovementRequest $request): JsonResponse
    {
        $data = $request->validated();
        $article = Article::findOrFail($data['article_id']);
        $warehouse = Warehouse::findOrFail($data['warehouse_id']);

        try {
            $movement = match ($data['type']) {
                StockMovementType::Entry->value => $this->movementService->recordEntry(
                    $article,
                    $warehouse,
                    $data['quantity'],
                    $data['unit_cost_ht'],
                    $data ?? []
                ),

                StockMovementType::Exit->value => $this->movementService->recordExit(
                    $article,
                    $warehouse,
                    $data['quantity'],
                    $data['project_id'],
                    $data ?? []
                ),

                StockMovementType::Return->value => $this->movementService->recordReturn(
                    $article,
                    $warehouse,
                    $data['quantity'],
                    $data['project_id'],
                    $data ?? []
                ),

                // AJOUT : Gestion explicite des ajustements d'inventaire
                StockMovementType::Adjustment->value => $this->movementService->recordAdjustment(
                    $article,
                    $warehouse,
                    $data['quantity'], // Note: Dans une UI réelle, prévoir un champ pour le signe ou le motif
                    $data['notes'] ?? 'Régularisation d\'inventaire',
                    $data ?? []
                ),

                StockMovementType::Transfer->value => $this->movementService->transfer(
                    $article,
                    $warehouse,
                    Warehouse::findOrFail($data['target_warehouse_id']),
                    $data['quantity'],
                    $data ?? []
                ),

                default => throw new \Exception('Type de mouvement non pris en charge par le système.'),
            };

            return response()->json($movement, 201);

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }
    }
}
