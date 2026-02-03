<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\InvoiceStatus;
use App\Enums\Commerce\InvoiceType;
use App\Models\Commerce\InvoiceItem;
use App\Models\Commerce\Invoices;
use App\Models\Commerce\Quote;
use App\Models\Commerce\QuoteItem;
use DB;

class InvoicingService
{
    /**
     * Génère une nouvelle situation de travaux à partir d'un devis.
     * Calcule automatiquement le montant de la période par rapport aux situations précédentes.
     */
    public function createProgressStatement(Quote $quote, int $situationNumber, array $progressData): Invoices
    {
        return DB::transaction(function () use ($quote, $situationNumber, $progressData) {

            // 1. Création de l'entête de la facture
            $invoice = Invoices::create([
                'tenants_id' => $quote->tenants_id,
                'tiers_id' => $quote->customer_id,
                'project_id' => $quote->project_id,
                'quote_id' => $quote->id,
                'type' => InvoiceType::Progress,
                'reference' => 'SIT-'.$situationNumber.'-'.$quote->reference,
                'situation_number' => $situationNumber,
                'due_date' => now()->addDays(30),
                'status' => InvoiceStatus::Draft,
                'retenue_garantie_pct' => 5.00, // Standard BTP
            ]);

            $totalHtPeriod = 0;

            // 2. Traitement des lignes d'avancement
            foreach ($progressData as $data) {
                $quoteItem = QuoteItem::findOrFail($data['quote_item_id']);
                $newTotalProgress = (float) $data['progress_percentage']; // Ex: 70%

                // Calcul du montant déjà facturé sur cette ligne lors des situations précédentes
                $alreadyInvoicedHt = $this->getAmountInvoicedToDate($quoteItem, $invoice->id);

                // Calcul du montant brut cumulé souhaité
                $cumulativeTotalHt = ($quoteItem->unit_price_ht * $quoteItem->quantity) * ($newTotalProgress / 100);

                // Le montant de la période est la différence
                $amountPeriodHt = $cumulativeTotalHt - $alreadyInvoicedHt;

                if ($amountPeriodHt != 0) {
                    InvoiceItem::create([
                        'invoices_id' => $invoice->id,
                        'quote_item_id' => $quoteItem->id,
                        'label' => $quoteItem->label,
                        'quantity' => 1, // Dans une situation, on raisonne souvent en montant
                        'unit_price_ht' => $amountPeriodHt,
                        'tax_rate' => 20.00,
                        'progress_percentage' => $newTotalProgress,
                    ]);

                    $totalHtPeriod += $amountPeriodHt;
                }
            }

            // 3. Application des calculs finaux (Taxes, RG, Prorata)
            $this->finalizeInvoiceTotals($invoice, $totalHtPeriod);

            return $invoice;
        });
    }

    /**
     * Récupère le montant total HT déjà facturé pour un item de devis.
     */
    public function getAmountInvoicedToDate(QuoteItem $quoteItem, ?int $excludeInvoiceId = null): float
    {
        return (float) InvoiceItem::where('quote_item_id', $quoteItem->id)
            ->whereHas('invoices', function ($q) use ($excludeInvoiceId) {
                $q->where('status', '!=', InvoiceStatus::Draft) // On ne compte que le validé/payé
                    ->when($excludeInvoiceId, fn ($query) => $query->where('id', '!=', $excludeInvoiceId));
            })
            ->sum(DB::raw('quantity * unit_price_ht'));
    }

    /**
     * Calcule les totaux, TVA, Retenue de Garantie et Net à payer.
     */
    protected function finalizeInvoiceTotals(Invoices $invoice, float $totalHt): void
    {
        $tva = $totalHt * 0.20;
        $ttc = $totalHt + $tva;

        // Calcul de la Retenue de Garantie (5% du TTC)
        $rgAmount = 0;
        if ($invoice->retenue_garantie_pct > 0) {
            $rgAmount = $ttc * ($invoice->retenue_garantie_pct / 100);
        }

        $invoice->update([
            'total_ht' => $totalHt,
            'total_tva' => $tva,
            'total_ttc' => $ttc,
            'retenue_garantie_amount' => $rgAmount,
        ]);
    }
}
