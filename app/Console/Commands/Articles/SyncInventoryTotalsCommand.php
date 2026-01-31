<?php

namespace App\Console\Commands\Articles;

use App\Enums\Articles\StockMovementType;
use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use DB;
use Illuminate\Console\Command;

class SyncInventoryTotalsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:sync-totals';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalcule les quantités en stock par dépôt à partir de l\'historique des mouvements.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Démarrage de la synchronisation des stocks...");
        $warehouses = Warehouse::all();
        Article::chunk(100, function ($articles) use ($warehouses) {
            foreach ($articles as $article) {
                foreach ($warehouses as $warehouse) {
                    $warehouseId = $warehouse->id;
                    // Calcul de la somme algébrique complexe :
                    // 1. Entrées, Retours et Ajustements (si warehouse_id est la source) -> Positif
                    // 2. Sorties (si warehouse_id est la source) -> Négatif
                    // 3. Transferts :
                    //    - Si warehouse_id est la SOURCE (warehouse_id) -> Négatif
                    //    - Si warehouse_id est la CIBLE (target_warehouse_id) -> Positif

                    $calculatedQty = DB::table('stock_movements')
                        ->where('article_id', $article->id)
                        ->where(function ($q) use ($warehouseId) {
                            $q->where('warehouse_id', $warehouseId)
                                ->orWhere('target_warehouse_id', $warehouseId);
                        })
                        ->selectRaw("SUM(
                    CASE
                        WHEN type IN ('" . StockMovementType::Entry->value . "', '" . StockMovementType::Return->value . "', '" . StockMovementType::Adjustment->value . "') AND warehouse_id = ? THEN quantity
                        WHEN type = '" . StockMovementType::Exit->value . "' AND warehouse_id = ? THEN -quantity
                        WHEN type = '" . StockMovementType::Transfer->value . "' AND warehouse_id = ? THEN -quantity
                        WHEN type = '" . StockMovementType::Transfer->value . "' AND target_warehouse_id = ? THEN quantity
                        ELSE 0
                    END
                ) as total", [$warehouseId, $warehouseId, $warehouseId, $warehouseId])
                        ->value('total') ?? 0;

                    // Mise à jour de la table pivot
                    // Note : On utilise syncWithoutDetaching ou updateExistingPivot pour préserver l'intégrité
                    $article->warehouses()->syncWithoutDetaching([
                        $warehouseId => ['quantity' => $calculatedQty]
                    ]);
                }
            }
        });

        $this->info("Synchronisation terminée avec succès.");
    }
}
