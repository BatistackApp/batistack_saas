<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\QuoteStatus;
use App\Models\Commerce\Quote;
use DB;
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
     * DUPLICATION DE DEVIS
     * Copie l'en-tête et l'ensemble des lignes de nomenclature.
     */
    public function duplicateQuote(Quote $quote): Quote
    {
        return DB::transaction(function () use ($quote) {
            // 1. Réplication de l'en-tête
            $newQuote = $quote->replicate();

            // On réinitialise les données pour un nouveau brouillon
            $newQuote->reference = $quote->reference . '-COPY'; // L'observer pourra écraser si nécessaire
            $newQuote->status = QuoteStatus::Draft;
            $newQuote->total_ht = $quote->total_ht;
            $newQuote->total_tva = $quote->total_tva;
            $newQuote->total_ttc = $quote->total_ttc;

            $newQuote->save();

            // 2. Réplication des lignes (items)
            foreach ($quote->load('items')->items as $item) {
                $newItem = $item->replicate();
                $newItem->quote_id = $newQuote->id;
                $newItem->save();
            }

            return $newQuote;
        });
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
