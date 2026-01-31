<?php

namespace App\Console\Commands\Articles;

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

        Article::chunk(100, function ($articles) {
            foreach ($articles as $article) {
                $warehouses = Warehouse::all();

                foreach ($warehouses as $warehouse) {
                    // Calcul de la somme algébrique des mouvements pour ce dépôt
                    $calculatedQty = DB::table('stock_movements')
                        ->where('article_id', $article->id)
                        ->where('warehouse_id', $warehouse->id)
                        ->select(DB::raw("SUM(CASE WHEN type IN ('entry', 'adj') THEN quantity ELSE -quantity END) as total"))
                        ->value('total') ?? 0;

                    // Mise à jour de la table pivot article_warehouse
                    $article->warehouses()->updateExistingPivot($warehouse->id, [
                        'quantity' => $calculatedQty
                    ]);
                }
            }
        });

        $this->info("Synchronisation terminée avec succès.");
    }
}
