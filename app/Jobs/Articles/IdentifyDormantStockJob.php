<?php

namespace App\Jobs\Articles;

use App\Models\Articles\Article;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class IdentifyDormantStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function handle(): void
    {
        $sixMonthsAgo = Carbon::now()->subMonths(6);

        // Articles n'ayant eu aucun mouvement depuis 6 mois
        $dormantArticles = Article::whereDoesHave('movements', function ($query) use ($sixMonthsAgo) {
            $query->where('created_at', '>', $sixMonthsAgo);
        })->get();

        foreach ($dormantArticles as $article) {
            Log::info("Stock Dormant détecté : {$article->sku} - {$article->name} (Dernier mouvement > 6 mois)");
            // On pourrait ici envoyer un rapport consolidé au service achat
        }
    }
}
