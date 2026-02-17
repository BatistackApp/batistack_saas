<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\QuoteItem;
use App\Services\Commerce\FinancialCalculatorService;

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
        app(FinancialCalculatorService::class)->updateDocumentTotals($item->quote);
    }
}
