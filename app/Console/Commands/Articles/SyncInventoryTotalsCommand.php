<?php

namespace App\Console\Commands\Articles;

use App\Enums\Articles\StockMovementType;
use App\Models\Articles\Article;
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
        $this->info('🔄 Démarrage de la synchronisation des stocks...');

        // 1. Calcul de l'impact net de tous les mouvements.
        // Utilisation d'un UNION ALL pour traiter un transfert comme deux lignes d'impact (Source et Cible).
        $movementsQuery = DB::table('stock_movements')
            ->select('article_id', 'warehouse_id', DB::raw("
                CASE
                    WHEN type IN ('".StockMovementType::Entry->value."', '".StockMovementType::Return->value."', '".StockMovementType::Adjustment->value."') THEN quantity
                    WHEN type IN ('".StockMovementType::Exit->value."', '".StockMovementType::Transfer->value."') THEN -quantity
                    ELSE 0
                END as impact
            "))
            ->unionAll(
                DB::table('stock_movements')
                    ->select('article_id', 'target_warehouse_id as warehouse_id', 'quantity as impact')
                    ->where('type', StockMovementType::Transfer->value)
                    ->whereNotNull('target_warehouse_id')
            );

        // 2. Agrégation des résultats par article et dépôt
        $totals = DB::table(DB::raw("({$movementsQuery->toSql()}) as combined_movements"))
            ->mergeBindings($movementsQuery)
            ->select('article_id', 'warehouse_id', DB::raw('SUM(impact) as total_qty'))
            ->groupBy('article_id', 'warehouse_id')
            ->get();

        $this->info('📊 Totaux calculés. Mise à jour de la base de données...');

        $bar = $this->output->createProgressBar($totals->count());

        DB::transaction(function () use ($totals, $bar) {
            // Remise à zéro propre des stocks avant ré-injection
            DB::table('article_warehouse')->update(['quantity' => 0]);

            $articleGlobalTotals = [];

            foreach ($totals as $row) {
                if (! $row->warehouse_id) {
                    continue;
                }

                // Mise à jour de la table pivot par dépôt
                DB::table('article_warehouse')
                    ->updateOrInsert(
                        ['article_id' => $row->article_id, 'warehouse_id' => $row->warehouse_id],
                        ['quantity' => $row->total_qty, 'updated_at' => now()]
                    );

                // Accumulation pour la mise à jour du total global sur l'article
                $articleGlobalTotals[$row->article_id] = ($articleGlobalTotals[$row->article_id] ?? 0) + $row->total_qty;

                $bar->advance();
            }

            // 3. Mise à jour de la colonne dénormalisée total_stock sur la table articles
            foreach ($articleGlobalTotals as $articleId => $total) {
                Article::where('id', $articleId)->update(['total_stock' => $total]);
            }
        });

        $bar->finish();
        $this->info("\n✅ Synchronisation terminée avec succès.");

        return Command::SUCCESS;
    }
}
