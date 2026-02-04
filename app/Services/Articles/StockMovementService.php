<?php

namespace App\Services\Articles;

use App\Enums\Articles\InventorySessionStatus;
use App\Enums\Articles\SerialNumberStatus;
use App\Enums\Articles\StockMovementType;
use App\Enums\Articles\TrackingType;
use App\Models\Articles\Article;
use App\Models\Articles\ArticleSerialNumber;
use App\Models\Articles\InventoryLine;
use App\Models\Articles\InventorySession;
use App\Models\Articles\Ouvrage;
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
     * Enregistre la consommation d'un ouvrage sur un chantier.
     * Déclenche l'explosion de la nomenclature et le déstockage des composants.
     *
     * * @return array Liste des mouvements de stock générés
     */
    public function recordOuvrageExit(Ouvrage $ouvrage, Warehouse $warehouse, float $ouvrageQty, int $projectId, array $options = []): array
    {
        return DB::transaction(function () use ($ouvrage, $warehouse, $ouvrageQty, $projectId, $options) {
            // Sécurité : Vérifier le gel du dépôt
            $this->ensureWarehouseIsNotFrozen($warehouse);

            $movements = [];

            // On charge les composants de la recette
            $ouvrage->load('components');

            if ($ouvrage->components->isEmpty()) {
                throw new Exception("L'ouvrage {$ouvrage->name} ne possède aucun composant dans sa nomenclature.");
            }

            foreach ($ouvrage->components as $component) {
                // 1. Quantité théorique pure
                $theoreticalQty = (float) $component->pivot->quantity_needed * $ouvrageQty;

                // 2. Application du facteur de perte (priorité : override > nomenclature > 0)
                $wastageFactor = $globalWastageOverride ?? ($component->pivot->wastage_factor_pct ?? 0);

                // Formule : Quantité réelle = Théorique * (1 + % perte)
                $realQtyToExit = $theoreticalQty * (1 + ($wastageFactor / 100));

                // Génération de la sortie pour l'article composant
                $movements[] = $this->recordExit(
                    $component,
                    $warehouse,
                    $realQtyToExit,
                    $projectId,
                    array_merge($options, [
                        'notes' => "Consommation via Ouvrage : {$ouvrage->name} (SKU: {$ouvrage->sku})",
                        'reference' => $options['reference'] ?? $ouvrage->sku,
                        'ouvrage_id' => $ouvrage->id, // Trace de l'origine pour l'audit
                    ])
                );
            }

            return $movements;
        });
    }

    /**
     * Ouvre une session d'inventaire pour un dépôt spécifique.
     * Cette action fige l'état théorique actuel (Snapshot).
     */
    public function openInventorySession(Warehouse $warehouse, ?string $notes = null): InventorySession
    {
        return DB::transaction(function () use ($warehouse, $notes) {
            // On vérifie si une session n'est pas déjà active pour ce dépôt
            $activeSession = InventorySession::where('warehouse_id', $warehouse->id)
                ->whereIn('status', [InventorySessionStatus::Open, InventorySessionStatus::Counting])
                ->exists();

            if ($activeSession) {
                throw new Exception("Une session d'inventaire est déjà en cours pour le dépôt {$warehouse->name}.");
            }

            $session = InventorySession::create([
                'tenants_id' => Auth::user()->tenants_id,
                'warehouse_id' => $warehouse->id,
                'status' => InventorySessionStatus::Open,
                'opened_at' => now(),
                'created_by' => Auth::id(),
                'notes' => $notes,
            ]);

            // Snapshot du stock théorique actuel pour ce dépôt
            $stockItems = DB::table('article_warehouse')
                ->where('warehouse_id', $warehouse->id)
                ->get();

            foreach ($stockItems as $item) {
                InventoryLine::create([
                    'inventory_session_id' => $session->id,
                    'article_id' => $item->article_id,
                    'theoretical_quantity' => $item->quantity,
                    'counted_quantity' => null, // En attente de saisie
                ]);
            }

            return $session;
        });
    }

    /**
     * Enregistre la quantité comptée physiquement pour un article.
     */
    public function recordInventoryCount(InventorySession $session, Article $article, float $countedQty): void
    {
        if (! in_array($session->status, [InventorySessionStatus::Open, InventorySessionStatus::Counting])) {
            throw new Exception('La saisie est verrouillée pour cette session.');
        }

        InventoryLine::updateOrCreate(
            ['inventory_session_id' => $session->id, 'article_id' => $article->id],
            ['counted_quantity' => $countedQty]
        );

        // Si c'est le premier comptage, on passe au statut 'Counting'
        if ($session->status === InventorySessionStatus::Open) {
            $session->update(['status' => InventorySessionStatus::Counting]);
        }
    }

    /**
     * Valide la session d'inventaire et génère les ajustements automatiques.
     */
    public function validateInventorySession(InventorySession $session): void
    {
        DB::transaction(function () use ($session) {
            if ($session->status === InventorySessionStatus::Validated) {
                throw new Exception('Cette session a déjà été validée.');
            }

            // On récupère les lignes présentant une différence
            $linesToAdjust = $session->lines()
                ->where('difference', '!=', 0)
                ->get();

            foreach ($linesToAdjust as $line) {
                if ($line->counted_quantity !== null) {
                    $this->recordAdjustment(
                        $line->article,
                        $session->warehouse,
                        (float) $line->difference,
                        "Régularisation Inventaire : {$session->reference}"
                    );
                }
            }

            $session->update([
                'status' => InventorySessionStatus::Validated,
                'validated_at' => now(),
                'validated_by' => Auth::id(),
                'closed_at' => now(),
            ]);
        });
    }

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

        if (! $article) {
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
            StockMovementType::Entry->value => $this->recordEntry($article, Warehouse::findOrFail($scanData['warehouse_id']), $scanData['quantity'], $scanData['unit_cost_ht'] ?? 0, $scanData),
            StockMovementType::Exit->value => $this->recordExit($article, Warehouse::findOrFail($scanData['warehouse_id']), $scanData['quantity'], $scanData['project_id'], $scanData),
            StockMovementType::Return->value => $this->recordReturn($article, Warehouse::findOrFail($scanData['warehouse_id']), $scanData['quantity'], $scanData['project_id'], $scanData),
            StockMovementType::Adjustment->value => $this->recordAdjustment($article, Warehouse::findOrFail($scanData['warehouse_id']), $scanData['quantity'], $scanData['notes'] ?? null, $scanData),
            default => throw new Exception('Action logistique non reconnue pour le mode scan.'),
        };
    }

    /**
     * Empêche les mouvements si un inventaire est en cours.
     */
    protected function ensureWarehouseIsNotFrozen(Warehouse $warehouse): void
    {
        $isFrozen = InventorySession::where('warehouse_id', $warehouse->id)
            ->whereIn('status', [InventorySessionStatus::Open, InventorySessionStatus::Counting])
            ->exists();

        if ($isFrozen) {
            throw new Exception("Action impossible : Le dépôt {$warehouse->name} est gelé pour inventaire.");
        }
    }

    /**
     * ENTRÉE / RÉCEPTION FOURNISSEUR
     * Gère la valorisation CUMP et l'enregistrement unitaire du matériel.
     */
    public function recordEntry(Article $article, Warehouse $warehouse, float $qty, float $priceHt, array $options = []): StockMovement
    {
        $this->ensureWarehouseIsNotFrozen($warehouse);

        return DB::transaction(function () use ($article, $warehouse, $qty, $priceHt, $options) {
            $snId = null;

            if ($article->tracking_type === TrackingType::SerialNumber) {
                // Pour le matériel sérialisé, on force la quantité à 1 par scan
                $snValue = $options['serial_number'] ?? ($options['scanned_code'] ?? null);

                if (! $snValue) {
                    throw new Exception("Numéro de série manquant pour l'entrée du matériel.");
                }

                $sn = ArticleSerialNumber::updateOrCreate(
                    [
                        'tenants_id' => Auth::user()->tenants_id,
                        'article_id' => $article->id,
                        'serial_number' => $snValue,
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
                'user_id' => Auth::id(),
            ]);
        });
    }

    /**
     * SORTIE / CONSOMMATION CHANTIER
     * Sortie nominative du matériel sérialisé vers un projet spécifique.
     */
    public function recordExit(Article $article, Warehouse $warehouse, float $qty, int $projectId, array $options = []): StockMovement
    {
        $this->ensureWarehouseIsNotFrozen($warehouse);
        if (! $this->inventoryService->hasEnoughStock($article, $warehouse, $qty)) {
            throw new Exception("Stock insuffisant dans le dépôt {$warehouse->name} pour l'article {$article->sku}.");
        }

        return DB::transaction(function () use ($article, $warehouse, $qty, $projectId, $options) {
            $snId = $options['serial_number_id'] ?? null;

            // Résolution automatique du SN si le code scanné correspond à un SN existant
            if ($article->tracking_type === TrackingType::SerialNumber && ! $snId) {
                $snId = ArticleSerialNumber::where('serial_number', $options['scanned_code'] ?? '')
                    ->where('article_id', $article->id)
                    ->where('tenants_id', Auth::user()->tenants_id)
                    ->value('id');
            }

            if ($article->tracking_type === TrackingType::SerialNumber && ! $snId) {
                throw new Exception('Veuillez flasher ou sélectionner un numéro de série valide pour ce matériel.');
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
                    'assigned_user_id' => Auth::id(),
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
                'user_id' => Auth::id(),
            ]);
        });
    }

    /**
     * RETOUR DE CHANTIER
     * Réintégration du matériel dans le dépôt à la fin d'une tâche.
     */
    public function recordReturn(Article $article, Warehouse $warehouse, float $qty, int $projectId, array $options = []): StockMovement
    {
        $this->ensureWarehouseIsNotFrozen($warehouse);

        return DB::transaction(function () use ($article, $warehouse, $qty, $projectId, $options) {
            $snId = $options['serial_number_id'] ?? null;

            if ($article->tracking_type === TrackingType::SerialNumber && ! $snId) {
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
                    'assigned_user_id' => null,
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
                'user_id' => Auth::id(),
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
                    'project_id' => null,
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
                'user_id' => Auth::id(),
            ]);
        });
    }

    /**
     * TRANSFERT INTER-DÉPÔTS
     * Déplacement de matériel entre dépôts ou véhicules de chantier.
     */
    public function transfer(Article $article, Warehouse $from, Warehouse $to, float $qty, array $options = []): void
    {
        $this->ensureWarehouseIsNotFrozen($from);
        $this->ensureWarehouseIsNotFrozen($to);

        if (! $this->inventoryService->hasEnoughStock($article, $from, $qty)) {
            throw new Exception('Transfert impossible : Stock source insuffisant.');
        }
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
                'user_id' => Auth::id(),
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
                'updated_at' => now(),
            ]);
        } else {
            $article->warehouses()->attach($warehouse->id, [
                'quantity' => $qtyDelta,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
