<?php

namespace App\Observers\Commerce;

use App\Jobs\Commerce\AccountFactureJob;
use App\Jobs\Commerce\ComputeFactureAmountsJob;
use App\Jobs\Commerce\GenerateFactureNumberJob;
use App\Models\Accounting\AccountingJournal;
use App\Models\Commerce\Facture;
use App\Services\Accounting\AutoPostingService;
use Illuminate\Support\Collection;

class FactureObserver
{
    public function __construct(private AutoPostingService $autoPostingService) {}

    public function creating(Facture $facture): void
    {
        if (! $facture->number) {
            GenerateFactureNumberJob::dispatch($facture);
        }
    }

    public function created(Facture $facture): void
    {
        ComputeFactureAmountsJob::dispatch($facture);
    }

    public function updating(Facture $facture): void
    {
        if ($facture->isDirty(['montant_ht', 'montant_tva', 'montant_ttc'])) {
            ComputeFactureAmountsJob::dispatch($facture);
        }
    }

    public function updated(Facture $facture): void
    {
        if ($facture->wasChanged('status') && $facture->status->value === 'validated') {
            AccountFactureJob::dispatch($facture);
        }

        if ($facture->wasChanged('status') && $facture->status->value === 'posted') {
            $this->createAccountingEntry($facture);
        }
    }

    public function deleting(Facture $facture): void
    {
        if ($facture->reglements()->exists()) {
            throw new \Exception('Impossible de supprimer une facture avec des règlements');
        }
    }

    private function createAccountingEntry(Facture $invoice): void
    {
        $tenant = $invoice->tenant;
        $journal = AccountingJournal::where('tenant_id', $tenant->id)
            ->where('code', 'VT')
            ->firstOrFail();

        $lines = $this->buildInvoiceLines($invoice);

        $this->autoPostingService->recordAndPost(
            $tenant,
            $journal,
            "Facture {$invoice->number}",
            $lines,
            $invoice->date_facture,
            'invoice',
            $invoice->id
        );
    }

    /**
     * Construit les lignes comptables à partir de la facture
     *
     * @return Collection<array{account_id: int, debit: float|string, credit: float|string, description: string}>
     */
    private function buildInvoiceLines(Facture $invoice): Collection
    {
        $customer = $invoice->tiers;
        $customerAccount = $customer->accountingAccount;

        $lines = collect();

        $totalTTC = $invoice->montant_ttc;
        $totalHT = $invoice->montant_ht;
        $totalVAT = $invoice->montant_tva;

        // Débit : Compte client
        $lines->push([
            'account_id' => $customerAccount->id,
            'debit' => $totalTTC,
            'credit' => 0,
            'description' => "Facture {$invoice->number}",
        ]);

        // Crédit : Compte de ventes
        $lines->push([
            'account_id' => $this->getSalesAccount($tenant = $invoice->tenant)->id,
            'debit' => 0,
            'credit' => $totalHT,
            'description' => "Vente service",
        ]);

        // Crédit : Compte TVA collectée
        if ($totalVAT > 0) {
            $lines->push([
                'account_id' => $this->getVATAccount($tenant)->id,
                'debit' => 0,
                'credit' => $totalVAT,
                'description' => "TVA collectée {$invoice->vat_rate}%",
            ]);
        }

        return $lines;
    }

    private function getSalesAccount($tenant): \App\Models\Accounting\AccountingAccounts
    {
        return \App\Models\Accounting\AccountingAccounts::where('tenant_id', $tenant->id)
            ->where('number', '701001')
            ->firstOrFail();
    }

    private function getVATAccount($tenant): \App\Models\Accounting\AccountingAccounts
    {
        return \App\Models\Accounting\AccountingAccounts::where('tenant_id', $tenant->id)
            ->where('number', '44571')
            ->firstOrFail();
    }
}
