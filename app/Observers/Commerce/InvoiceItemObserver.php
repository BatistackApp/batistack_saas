<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\InvoiceItem;
use App\Services\Commerce\FinancialCalculatorService;

class InvoiceItemObserver
{
    public function saved(InvoiceItem $invoiceItem): void
    {
        $this->updateParent($invoiceItem);
    }

    public function deleted(InvoiceItem $invoiceItem): void
    {
        $this->updateParent($invoiceItem);
    }

    protected function updateParent(InvoiceItem $item): void
    {
        app(FinancialCalculatorService::class)->updateDocumentTotals($item->invoices);
    }
}
