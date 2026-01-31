<?php

namespace App\Services\Articles;

use App\Enums\Articles\StockMovementType;
use App\Models\Articles\Article;
use App\Models\Articles\StockMovement;
use App\Models\Articles\Warehouse;
use DB;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Service gérant l'exécution des flux physiques de marchandises.
 */
class StockMovementService
{
    public function __construct(protected InventoryService $inventoryService) {}

    /**
     * Enregistre une entrée en stock (Réception fournisseur).
     */
    public function recordEntry(Article $article, Warehouse $warehouse, float $qty, float $priceHt, ?string $ref = null): StockMovement
    {
        return DB::transaction(function () use ($article, $warehouse, $qty, $priceHt, $ref) {
            // 1. Recalcul de la valorisation avant de modifier les stocks
            $this->inventoryService->updateCump($article, $qty, $priceHt);

            // 2. Mise à jour du stock physique (Incrémentation)
            $this->updateArticleWarehouseStock($article, $warehouse, $qty);

            // 3. Création du mouvement de traçabilité
            return StockMovement::create([
                'tenants_id' => Auth::user()->tenants_id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::Entry,
                'quantity' => $qty,
                'unit_cost_ht' => $priceHt,
                'reference' => $ref,
                'user_id' => Auth::id()
            ]);
        });
    }

    /**
     * Enregistre une sortie de stock (Consommation chantier).
     */
    public function recordExit(Article $article, Warehouse $warehouse, float $qty, int $projectId, ?int $phaseId = null): StockMovement
    {
        if (!$this->inventoryService->hasEnoughStock($article, $warehouse, $qty)) {
            throw new Exception("Stock insuffisant dans le dépôt {$warehouse->name} pour l'article {$article->sku}.");
        }

        return DB::transaction(function () use ($article, $warehouse, $qty, $projectId, $phaseId) {
            // 1. Décrémentation du stock
            $this->updateArticleWarehouseStock($article, $warehouse, -$qty);

            // 2. Création du mouvement (Valorisé au CUMP actuel)
            return StockMovement::create([
                'tenants_id' => Auth::user()->tenants_id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'project_id' => $projectId,
                'project_phase_id' => $phaseId,
                'type' => StockMovementType::Exit,
                'quantity' => $qty,
                'unit_cost_ht' => $article->cump_ht,
                'user_id' => Auth::id()
            ]);
        });
    }

    /**
     * Effectue un transfert atomique entre deux dépôts.
     */
    public function transfer(Article $article, Warehouse $from, Warehouse $to, float $qty): StockMovement
    {
        if (!$this->inventoryService->hasEnoughStock($article, $from, $qty)) {
            throw new Exception("Transfert impossible : Stock source insuffisant.");
        }

        DB::transaction(function () use ($article, $from, $to, $qty) {
            $this->updateArticleWarehouseStock($article, $from, -$qty);
            $this->updateArticleWarehouseStock($article, $to, $qty);

            return StockMovement::create([
                'tenants_id' => Auth::user()->tenants_id,
                'article_id' => $article->id,
                'warehouse_id' => $from->id,
                'target_warehouse_id' => $to->id,
                'type' => StockMovementType::Transfer,
                'quantity' => $qty,
                'unit_cost_ht' => $article->cump_ht,
                'user_id' => Auth::id()
            ]);
        });
    }

    /**
     * Met à jour techniquement la table pivot article_warehouse.
     */
    protected function updateArticleWarehouseStock(Article $article, Warehouse $warehouse, float $qtyDelta): void
    {
        $pivot = $article->warehouses()->where('warehouse_id', $warehouse->id)->first();

        if ($pivot) {
            $article->warehouses()->updateExistingPivot($warehouse->id, [
                'quantity' => $pivot->pivot->quantity + $qtyDelta
            ]);
        } else {
            $article->warehouses()->attach($warehouse->id, ['quantity' => $qtyDelta]);
        }
    }
}
