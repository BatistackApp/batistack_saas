<?php

namespace App\Jobs\Articles;

use App\Models\Articles\Article;
use App\Models\Articles\Ouvrage;
use App\Models\User;
use App\Notifications\Articles\OuvrageCostVariationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;
use Notification;

class RecalculateOuvrageCostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Article $article, protected float $oldCump) {}

    public function handle(): void
    {
        // On récupère tous les ouvrages qui utilisent cet article dans leur recette
        $ouvrages = Ouvrage::whereHas('components', function ($query) {
            $query->where('article_id', $this->article->id);
        })->with('components')->get();

        $recipients = User::role(['tenant_admin', 'economiste'])->get();

        foreach ($ouvrages as $ouvrage) {
            $newCump = (float) $this->article->cump_ht;
            $qtyNeeded = (float) $ouvrage->components->where('id', $this->article->id)->first()->pivot->quantity_needed;

            // Calcul de l'impact sur le coût total de l'ouvrage
            // Coût total actuel (avec le nouveau CUMP déjà en base)
            $newTotalCost = $ouvrage->theoretical_cost;

            // Calcul de l'ancien coût total pour la comparaison
            $oldTotalCost = $newTotalCost - ($qtyNeeded * $newCump) + ($qtyNeeded * $this->oldCump);

            // 2. Seuil d'alerte : Si la variation est > 5%
            $variationPercent = (($newTotalCost - $oldTotalCost) / $oldTotalCost) * 100;

            if (abs($variationPercent) >= 5.0 && $recipients->isNotEmpty()) {
                Notification::send(
                    $recipients,
                    new OuvrageCostVariationNotification($ouvrage, $oldTotalCost, $newTotalCost)
                );
            }
        }
    }
}
