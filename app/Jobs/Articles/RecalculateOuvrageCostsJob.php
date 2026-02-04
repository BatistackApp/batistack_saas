<?php

namespace App\Jobs\Articles;

use App\Enums\Commerce\QuoteStatus;
use App\Models\Articles\Article;
use App\Models\Articles\Ouvrage;
use App\Models\Commerce\QuoteItem;
use App\Models\User;
use App\Notifications\Articles\OuvrageCostVariationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class RecalculateOuvrageCostsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Article $article, protected float $oldCump) {}

    public function handle(): void
    {
        // 1. Identifier les ouvrages utilisant cet article
        $ouvrages = Ouvrage::whereHas('components', function ($query) {
            $query->where('article_id', $this->article->id);
        })->get();

        foreach ($ouvrages as $ouvrage) {
            $newCost = $ouvrage->theoretical_cost;
            $oldCost = $newCost - ($this->article->cump_ht - $this->oldCump); // Estimation simplifiée

            $variationPercent = (($newCost - $oldCost) / $oldCost) * 100;

            // 2. Si la variation est significative (> 3%), on impacte les devis
            if (abs($variationPercent) >= 3.0) {
                $this->markOutdatedQuoteItems($ouvrage, $variationPercent, $newCost);
                $this->notifyStakeholders($ouvrage, $oldCost, $newCost);
            }
        }
    }

    /**
     * Marque les lignes de devis en cours comme "À réévaluer".
     */
    protected function markOutdatedQuoteItems(Ouvrage $ouvrage, float $variation, float $newCost): void
    {
        QuoteItem::where('ouvrage_id', $ouvrage->id)
            ->whereHas('quote', function ($q) {
                // On ne cible que les devis non signés (Draft ou Sent)
                $q->whereIn('status', [QuoteStatus::Draft, QuoteStatus::Sent]);
            })
            ->update([
                'is_cost_outdated' => true,
                'cost_variation_pct' => $variation,
                'last_known_cost_ht' => $newCost,
            ]);
    }

    protected function notifyStakeholders(Ouvrage $ouvrage, float $old, float $new): void
    {
        $recipients = User::role(['tenant_admin', 'economiste'])->get();
        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new OuvrageCostVariationNotification($ouvrage, $old, $new));
        }
    }
}
