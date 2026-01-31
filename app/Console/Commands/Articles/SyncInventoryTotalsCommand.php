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

        DB::table('article_warehouse')->update(['quantity' => 0]);

        // Calcul atomique de tous les totaux en une seule requête
        $totals = DB::table('stock_movements')
            ->select(
                'article_id',
                DB::raw("
                    CASE
                        WHEN type = '" . StockMovementType::Transfer->value . "' THEN target_warehouse_id
                        ELSE warehouse_id
                    END as effective_warehouse_id
                "),
                DB::raw("
                    SUM(
                        CASE
                            WHEN type IN ('" . StockMovementType::Entry->value . "', '" . StockMovementType::Return->value . "', '" . StockMovementType::Adjustment->value . "')
                                AND target_warehouse_id IS NULL THEN quantity
                            WHEN type = '" . StockMovementType::Exit->value . "' THEN -quantity
                            WHEN type = '" . StockMovementType::Transfer->value . "' AND target_warehouse_id IS NULL THEN -quantity
                            WHEN type = '" . StockMovementType::Transfer->value . "' AND target_warehouse_id IS NOT NULL THEN quantity
                            ELSE 0
                        END
                    ) as total_qty
                ")
            )
            ->groupBy('article_id', 'effective_warehouse_id')
            ->get()
            ->keyBy(function ($row) {
                // Clé unique pour un accès rapide
                return "{$row->article_id}-{$row->effective_warehouse_id}";
            });

        $this->info("Totaux calculés. Démarrage de la mise à jour des stocks...");
        $bar = $this->output->createProgressBar(DB::table('article_warehouse')->count());

        DB::transaction(function () use ($totals, $bar) {
            // Remise à zéro de toutes les quantités en une seule fois
            DB::table('article_warehouse')->update(['quantity' => 0]);

            // Mise à jour ciblée
            foreach ($totals as $key => $calculated) {
                [$articleId, $warehouseId] = explode('-', $key);

                DB::table('article_warehouse')
                    ->where('article_id', $articleId)
                    ->where('warehouse_id', $warehouseId)
                    ->update([
                        'quantity' => (float) $calculated->total_qty,
                        'updated_at' => now()
                    ]);
                $bar->advance(); // Optionnel : pour le suivi
            }
        });

        $bar->finish();
        $this->info("\nSynchronisation terminée avec succès.");
    }
}
