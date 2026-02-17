<?php

namespace App\Jobs\Articles;

use App\Models\Articles\Article;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendStockShortageReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $shortageArticles = Article::whereColumn('total_stock', '<=', 'alert_stock')
            ->where('alert_stock', '>', 0) // Évite les alertes si le seuil est à 0
            ->get();

        if ($shortageArticles->isNotEmpty()) {
            $recipients = User::role('logistics_manager')->get();
            // Logique d'envoi d'un email récapitulatif (Mailable) ici
            Log::info('Rapport de rupture envoyé pour '.$shortageArticles->count().' articles.');
        }
    }
}
