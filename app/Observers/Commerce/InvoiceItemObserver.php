<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\InvoiceItem;

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
        $invoice = $item->invoices;
        $totals = $invoice->items()
            ->selectRaw('SUM(quantity * unit_price_ht) as ht, SUM(quantity * unit_price_ht * (tax_rate / 100)) as tva')
            ->first();

        $totalHt = (float) $totals->ht;
        $totalTva = (float) $totals->tva;
        $totalTtc = $totalHt + $totalTva;

        $invoice->update([
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => $totalTtc,
            // Le montant de la retenue sera mis à jour par le InvoicesObserver@saving
        ]);
    }
}
