<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\QuoteItem;

class QuoteItemObserver
{
    public function saved(QuoteItem $quoteItem): void
    {
        $this->updateParent($quoteItem);
    }

    public function deleted(QuoteItem $quoteItem): void
    {
        $this->updateParent($quoteItem);
    }

    protected function updateParent(QuoteItem $item): void
    {
        $quote = $item->quote;
        $totals = $quote->items()
            ->selectRaw('SUM(quantity * unit_price_ht) as ht')
            ->first();

        $totalHt = (float) $totals->ht;
        $totalTva = $totalHt * 0.20; // Hypothèse TVA standard, à ajuster si par ligne

        $quote->update([
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => $totalHt + $totalTva,
        ]);
    }
}
