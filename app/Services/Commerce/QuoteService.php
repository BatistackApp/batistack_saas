<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\QuoteStatus;
use App\Models\Commerce\Quote;
use Exception;

class QuoteService
{
    /**
     * Accepte un devis et prépare le projet pour l'exécution.
     */
    public function acceptQuote(Quote $quote): void
    {
        if ($quote->status !== QuoteStatus::Sent) {
            throw new Exception('Seul un devis envoyé peut être accepté.');
        }

        $quote->update(['status' => QuoteStatus::Accepted]);

        // Optionnel : Déclencher un événement pour informer le module Chantier
        // event(new QuoteAccepted($quote));
    }

    /**
     * Calcule le total d'un devis à partir de ses items.
     */
    public function refreshQuoteTotals(Quote $quote): void
    {
        $totalHt = $quote->items->sum(fn ($item) => $item->quantity * $item->unit_price_ht);
        $totalTva = $totalHt * 0.20;

        $quote->update([
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => $totalHt + $totalTva,
        ]);
    }
}
