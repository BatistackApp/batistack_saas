<?php

namespace App\Services\Articles;

use App\Enums\Articles\AdjustementType;
use App\Enums\Articles\SerialNumberStatus;
use App\Enums\Articles\StockMovementType;
use App\Enums\Articles\TrackingType;
use App\Models\Articles\Article;
use App\Models\Articles\ArticleSerialNumber;
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
     * Gère la création/mise à jour du Numéro de Série si l'article est sérialisé.
     */
    public function recordEntry(Article $article, Warehouse $warehouse, float $qty, float $priceHt, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($article, $warehouse, $qty, $priceHt, $options) {

            $snId = null;

            // Logique spécifique aux articles sérialisés (TrackingType::SerialNumber)
            if ($article->tracking_type === TrackingType::SerialNumber) {
                if ($qty != 1) {
                    throw new Exception("Un article suivi par numéro de série doit être enregistré à l'unité (quantité = 1).");
                }

                $sn = ArticleSerialNumber::updateOrCreate(
                    [
                        'tenants_id' => Auth::user()->tenants_id,
                        'article_id' => $article->id,
                        'serial_number' => $options['serial_number'] ?? throw new Exception("Le numéro de série est requis pour cet article."),
                    ],
                    [
                        'warehouse_id' => $warehouse->id,
                        'status' => SerialNumberStatus::InStock,
                        'purchase_date' => $options['purchase_date'] ?? now(),
                        'warranty_expiry' => $options['warranty_expiry'] ?? null,
                    ]
                );
                $snId = $sn->id;
            }

            // 1. Recalcul de la valorisation (CUMP)
            $this->inventoryService->updateCump($article, $qty, $priceHt);

            // 2. Mise à jour du stock physique par dépôt
            $this->updateArticleWarehouseStock($article, $warehouse, $qty);

            // 3. Création du mouvement de traçabilité
            return StockMovement::create([
                'tenants_id' => Auth::user()->tenants_id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'serial_number_id' => $snId,
                'type' => StockMovementType::Entry,
                'quantity' => $qty,
                'unit_cost_ht' => $priceHt,
                'reference' => $options['reference'] ?? null,
                'user_id' => Auth::id()
            ]);
        });
    }

    /**
     * Enregistre une sortie de stock (Consommation chantier).
     * Si l'article est sérialisé, met à jour l'état de la machine (Affectée au projet).
     */
    public function recordExit(Article $article, Warehouse $warehouse, float $qty, int $projectId, array $options = []): StockMovement
    {
        if (!$this->inventoryService->hasEnoughStock($article, $warehouse, $qty)) {
            throw new Exception("Stock insuffisant dans le dépôt {$warehouse->name} pour l'article {$article->sku}.");
        }

        return DB::transaction(function () use ($article, $warehouse, $qty, $projectId, $options) {

            $snId = $options['serial_number_id'] ?? null;

            if ($article->tracking_type === TrackingType::SerialNumber) {
                if (!$snId) throw new Exception("Veuillez sélectionner le numéro de série spécifique à sortir.");

                $sn = ArticleSerialNumber::where('id', $snId)
                    ->where('tenants_id', Auth::user()->tenants_id)
                    ->firstOrFail();

                if ($sn->status !== SerialNumberStatus::InStock || $sn->warehouse_id !== $warehouse->id) {
                    throw new Exception("Ce matériel (SN: {$sn->serial_number}) n'est pas disponible dans ce dépôt.");
                }

                // Sortie du matériel vers le chantier
                $sn->update([
                    'status' => SerialNumberStatus::Assigned,
                    'warehouse_id' => null,
                    'project_id' => $projectId,
                    'assigned_user_id' => Auth::id()
                ]);
            }

            // Mise à jour du stock (Décrémentation)
            $this->updateArticleWarehouseStock($article, $warehouse, -$qty);

            return StockMovement::create([
                'tenants_id' => Auth::user()->tenants_id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'serial_number_id' => $snId,
                'project_id' => $projectId,
                'project_phase_id' => $options['project_phase_id'] ?? null,
                'type' => StockMovementType::Exit,
                'quantity' => $qty,
                'unit_cost_ht' => $article->cump_ht,
                'user_id' => Auth::id()
            ]);
        });
    }

    /**
     * Enregistre un retour de chantier (Réintégration en stock).
     */
    public function recordReturn(Article $article, Warehouse $warehouse, float $qty, int $projectId, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($article, $warehouse, $qty, $projectId, $options) {

            $snId = $options['serial_number_id'] ?? null;

            if ($article->tracking_type === TrackingType::SerialNumber) {
                if (!$snId) throw new Exception("Veuillez identifier le matériel retourné via son numéro de série.");

                $sn = ArticleSerialNumber::findOrFail($snId);
                $sn->update([
                    'status' => SerialNumberStatus::InStock,
                    'warehouse_id' => $warehouse->id,
                    'project_id' => null,
                    'assigned_user_id' => null
                ]);
            }

            $this->updateArticleWarehouseStock($article, $warehouse, $qty);

            return StockMovement::create([
                'tenants_id' => Auth::user()->tenants_id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'serial_number_id' => $snId,
                'project_id' => $projectId,
                'type' => StockMovementType::Return,
                'quantity' => $qty,
                'unit_cost_ht' => $article->cump_ht,
                'user_id' => Auth::id()
            ]);
        });
    }

    /**
     * Enregistre un ajustement d'inventaire (Gain ou Perte).
     * Gère les quantités signées et le statut des SN.
     */
    public function recordAdjustment(Article $article, Warehouse $warehouse, float $qty, ?string $notes = null, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($article, $warehouse, $qty, $notes, $options) {

            $snId = $options['serial_number_id'] ?? null;

            if ($article->tracking_type === TrackingType::SerialNumber && $snId) {
                $sn = ArticleSerialNumber::findOrFail($snId);
                // Si qty < 0, on marque comme perdu. Si qty > 0, on marque comme en stock.
                $sn->update([
                    'status' => $qty < 0 ? SerialNumberStatus::Lost : SerialNumberStatus::InStock,
                    'warehouse_id' => $qty < 0 ? null : $warehouse->id,
                ]);
            }

            $this->updateArticleWarehouseStock($article, $warehouse, $qty);

            return StockMovement::create([
                'tenants_id' => Auth::user()->tenants_id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'serial_number_id' => $snId,
                'type' => StockMovementType::Adjustment,
                'quantity' => $qty,
                'unit_cost_ht' => $article->cump_ht,
                'notes' => $notes,
                'user_id' => Auth::id()
            ]);
        });
    }

    /**
     * Effectue un transfert atomique entre deux dépôts.
     */
    public function transfer(Article $article, Warehouse $from, Warehouse $to, float $qty, array $options = []): StockMovement
    {
        if (!$this->inventoryService->hasEnoughStock($article, $from, $qty)) {
            throw new Exception("Transfert impossible : Stock source insuffisant.");
        }

        return DB::transaction(function () use ($article, $from, $to, $qty, $options) {

            $snId = $options['serial_number_id'] ?? null;

            if ($article->tracking_type === TrackingType::SerialNumber) {
                if (!$snId) throw new Exception("Le numéro de série est obligatoire pour transférer ce matériel.");

                $sn = ArticleSerialNumber::findOrFail($snId);
                $sn->update(['warehouse_id' => $to->id]);
            }

            $this->updateArticleWarehouseStock($article, $from, -$qty);
            $this->updateArticleWarehouseStock($article, $to, $qty);

            return StockMovement::create([
                'tenants_id' => Auth::user()->tenants_id,
                'article_id' => $article->id,
                'warehouse_id' => $from->id,
                'target_warehouse_id' => $to->id,
                'serial_number_id' => $snId,
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
