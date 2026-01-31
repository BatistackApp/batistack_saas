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

        // 1. Calculer tous les totaux en une seule requête
        $totals = DB::table('stock_movements')
            ->select('article_id',
                DB::raw("CASE
                        WHEN type = '".StockMovementType::Transfer->value."' THEN target_warehouse_id
                        ELSE warehouse_id
                     END as effective_warehouse_id"),
                DB::raw("SUM(
                CASE
                    WHEN type IN ('".StockMovementType::Entry->value."', '".StockMovementType::Return->value."', '".StockMovementType::Adjustment->value."') AND target_warehouse_id IS NULL THEN quantity
                    WHEN type = '".StockMovementType::Exit->value."' THEN -quantity
                    WHEN type = '".StockMovementType::Transfer->value."' THEN -quantity
                    WHEN type = '".StockMovementType::Transfer->value."' AND target_warehouse_id IS NOT NULL THEN quantity
                    ELSE 0
                END
            ) as total_qty")
            )
            ->groupBy('article_id', 'effective_warehouse_id')
            ->get()
            ->keyBy(fn($row) => $row->article_id . '-' . $row->effective_warehouse_id);

        // 2. Mettre à jour la table pivot
        // Il est préférable de le faire par chunk pour éviter les problèmes de mémoire sur de grandes tables
        DB::table('article_warehouse')->orderBy('id')->chunk(200, function ($pivotEntries) use ($totals) {
            $updates = [];
            foreach ($pivotEntries as $entry) {
                $key = $entry->article_id . '-' . $entry->warehouse_id;
                $newQty = $totals->get($key)?->total_qty ?? 0;

                if ((float)$entry->quantity !== (float)$newQty) {
                    // Utiliser une requête UPDATE groupée si possible ou une par une
                    DB::table('article_warehouse')
                        ->where('id', $entry->id)
                        ->update(['quantity' => $newQty]);
                }
            }
        });

        $this->info("Synchronisation terminée avec succès.");
    }
}
