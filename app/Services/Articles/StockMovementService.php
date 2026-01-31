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
     * Résout un article à partir d'un code scanné (Barcode, QR ou SKU).
     * Utilisé comme point d'entrée pour les terminaux mobiles de chantier.
     */
    public function resolveArticleByCode(string $code): Article
    {
        $article = Article::where('qr_code_base', $code)
            ->orWhere('barcode', $code)
            ->orWhere('sku', $code)
            ->first();

        if (!$article) {
            throw new Exception("L'identifiant scanné ne correspond à aucun article du catalogue : {$code}");
        }

        return $article;
    }

    /**
     * Enregistre un mouvement à partir de données brutes issues d'un scan.
     * Détermine automatiquement s'il s'agit d'un article ou d'un numéro de série spécifique.
     */
    public function recordFromScan(array $scanData): StockMovement
    {
        $article = $this->resolveArticleByCode($scanData['scanned_code']);

        return match ($scanData['type']) {
            StockMovementType::Entry->value      => $this->recordEntry($article, Warehouse::findOrFail($scanData['warehouse_id']), $scanData['quantity'], $scanData['unit_cost_ht'] ?? 0, $scanData),
            StockMovementType::Exit->value       => $this->recordExit($article, Warehouse::findOrFail($scanData['warehouse_id']), $scanData['quantity'], $scanData['project_id'], $scanData),
            StockMovementType::Return->value     => $this->recordReturn($article, Warehouse::findOrFail($scanData['warehouse_id']), $scanData['quantity'], $scanData['project_id'], $scanData),
            StockMovementType::Adjustment->value => $this->recordAdjustment($article, Warehouse::findOrFail($scanData['warehouse_id']), $scanData['quantity'], $scanData['notes'] ?? null, $scanData),
            default => throw new Exception("Action logistique non reconnue pour le mode scan."),
        };
    }

    /**
     * ENTRÉE / RÉCEPTION FOURNISSEUR
     * Gère la valorisation CUMP et l'enregistrement unitaire du matériel.
     */
    public function recordEntry(Article $article, Warehouse $warehouse, float $qty, float $priceHt, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($article, $warehouse, $qty, $priceHt, $options) {
            $snId = null;

            if ($article->tracking_type === TrackingType::SerialNumber) {
                // Pour le matériel sérialisé, on force la quantité à 1 par scan
                $snValue = $options['serial_number'] ?? ($options['scanned_code'] ?? null);

                if (!$snValue) {
                    throw new Exception("Numéro de série manquant pour l'entrée du matériel.");
                }

                $sn = ArticleSerialNumber::updateOrCreate(
                    [
                        'tenants_id' => Auth::user()->tenants_id,
                        'article_id' => $article->id,
                        'serial_number' => $snValue
                    ],
                    [
                        'warehouse_id' => $warehouse->id,
                        'status' => SerialNumberStatus::InStock,
                        'purchase_date' => $options['purchase_date'] ?? now(),
                    ]
                );
                $snId = $sn->id;
                $qty = 1; // Contrainte unitaire
            }

            // Mise à jour financière et physique
            $this->inventoryService->updateCump($article, $qty, $priceHt);
            $this->updateArticleWarehouseStock($article, $warehouse, $qty);

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
     * SORTIE / CONSOMMATION CHANTIER
     * Sortie nominative du matériel sérialisé vers un projet spécifique.
     */
    public function recordExit(Article $article, Warehouse $warehouse, float $qty, int $projectId, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($article, $warehouse, $qty, $projectId, $options) {
            $snId = $options['serial_number_id'] ?? null;

            // Résolution automatique du SN si le code scanné correspond à un SN existant
            if ($article->tracking_type === TrackingType::SerialNumber && !$snId) {
                $snId = ArticleSerialNumber::where('serial_number', $options['scanned_code'] ?? '')
                    ->where('article_id', $article->id)
                    ->where('tenants_id', Auth::user()->tenants_id)
                    ->value('id');
            }

            if ($article->tracking_type === TrackingType::SerialNumber && !$snId) {
                throw new Exception("Veuillez flasher ou sélectionner un numéro de série valide pour ce matériel.");
            }

            if ($snId) {
                $sn = ArticleSerialNumber::findOrFail($snId);

                // Vérification de disponibilité
                if ($sn->status !== SerialNumberStatus::InStock) {
                    throw new Exception("Opération impossible : Le matériel (SN: {$sn->serial_number}) est actuellement marqué comme '{$sn->status->value}'.");
                }

                $sn->update([
                    'status' => SerialNumberStatus::Assigned,
                    'warehouse_id' => null,
                    'project_id' => $projectId,
                    'assigned_user_id' => Auth::id()
                ]);
                $qty = 1;
            }

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
     * RETOUR DE CHANTIER
     * Réintégration du matériel dans le dépôt à la fin d'une tâche.
     */
    public function recordReturn(Article $article, Warehouse $warehouse, float $qty, int $projectId, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($article, $warehouse, $qty, $projectId, $options) {
            $snId = $options['serial_number_id'] ?? null;

            if ($article->tracking_type === TrackingType::SerialNumber && !$snId) {
                $snId = ArticleSerialNumber::where('serial_number', $options['scanned_code'] ?? '')
                    ->where('article_id', $article->id)
                    ->value('id');
            }

            if ($snId) {
                $sn = ArticleSerialNumber::findOrFail($snId);
                $sn->update([
                    'status' => SerialNumberStatus::InStock,
                    'warehouse_id' => $warehouse->id,
                    'project_id' => null,
                    'assigned_user_id' => null
                ]);
                $qty = 1;
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
     * AJUSTEMENT / INVENTAIRE
     * Correction des stocks signée (quantité positive ou négative).
     */
    public function recordAdjustment(Article $article, Warehouse $warehouse, float $qty, ?string $notes = null, array $options = []): StockMovement
    {
        return DB::transaction(function () use ($article, $warehouse, $qty, $notes, $options) {
            $snId = $options['serial_number_id'] ?? null;

            if ($article->tracking_type === TrackingType::SerialNumber && $snId) {
                $sn = ArticleSerialNumber::findOrFail($snId);
                $sn->update([
                    'status' => $qty < 0 ? SerialNumberStatus::Lost : SerialNumberStatus::InStock,
                    'warehouse_id' => $qty < 0 ? null : $warehouse->id,
                    'project_id' => null
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
                'notes' => $notes ?? 'Ajustement d\'inventaire scanné',
                'user_id' => Auth::id()
            ]);
        });
    }

    /**
     * TRANSFERT INTER-DÉPÔTS
     * Déplacement de matériel entre dépôts ou véhicules de chantier.
     */
    public function transfer(Article $article, Warehouse $from, Warehouse $to, float $qty, array $options = []): void
    {
        DB::transaction(function () use ($article, $from, $to, $qty, $options) {
            $snId = $options['serial_number_id'] ?? null;

            if ($article->tracking_type === TrackingType::SerialNumber && $snId) {
                $sn = ArticleSerialNumber::findOrFail($snId);
                $sn->update(['warehouse_id' => $to->id]);
            }

            $this->updateArticleWarehouseStock($article, $from, -$qty);
            $this->updateArticleWarehouseStock($article, $to, $qty);

            StockMovement::create([
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
     * Mise à jour technique de la table pivot (quantité par dépôt).
     */
    protected function updateArticleWarehouseStock(Article $article, Warehouse $warehouse, float $qtyDelta): void
    {
        $pivot = $article->warehouses()->where('warehouse_id', $warehouse->id)->first();
        if ($pivot) {
            $article->warehouses()->updateExistingPivot($warehouse->id, [
                'quantity' => $pivot->pivot->quantity + $qtyDelta,
                'updated_at' => now()
            ]);
        } else {
            $article->warehouses()->attach($warehouse->id, [
                'quantity' => $qtyDelta,
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
}
