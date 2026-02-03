<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\Invoices;
use App\Services\Commerce\FinancialCalculatorService;

class InvoicesObserver
{
    public function creating(Invoices $invoice): void
    {
        if (empty($invoice->reference)) {
            $prefix = $invoice->type->value === 'situation' ? 'SIT' : 'FAC';
            $year = date('Y');
            $count = Invoices::whereYear('created_at', $year)->count() + 1;
            $invoice->reference = "{$prefix}-{$year}-".str_pad($count, 5, '0', STR_PAD_LEFT);
        }

        if (empty($invoice->due_date)) {
            $invoice->due_date = now()->addDays(30);
        }
    }

    public function saving(Invoices $invoice): void
    {
        // Recalcul de la retenue de garantie si le pourcentage change
        app(FinancialCalculatorService::class)->updateDocumentTotals($invoice);
    }
}
