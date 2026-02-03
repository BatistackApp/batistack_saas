<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\QuoteItem;
use DB;

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
        $totals = DB::table('quote_items')
            ->where('quote_id', $quote->id)
            ->selectRaw('
                SUM(quantity * unit_price_ht) as ht,
                SUM(quantity * unit_price_ht * (tax_rate / 100)) as tva
            ')->first();

        $totalHt = (float) ($totals->ht ?? 0);
        $totalTva = (float) ($totals->tva ?? 0);

        $quote->update([
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => $totalHt + $totalTva,
        ]);
    }
}
