<?php

namespace App\Observers\Commerce;

use App\Enums\Commerce\InvoiceType;
use App\Models\Commerce\Invoices;
use App\Services\Commerce\FinancialCalculatorService;
use App\Services\Core\TenantConfigService;

class InvoicesObserver
{
    public function __construct(protected FinancialCalculatorService $calculator) {}

    public function creating(Invoices $invoice): void
    {
        $tenant = $invoice->tenant;

        // Initialisation de la RG par défaut
        if ($invoice->type === InvoiceType::Progress && $invoice->retenue_garantie_pct == 0) {
            $invoice->retenue_garantie_pct = TenantConfigService::get($tenant, 'commerce.invoices.retenue_garantie_pct', 5.00);
        }

        // INITIALISATION DU COMPTE PRORATA par défaut
        if ($invoice->type === InvoiceType::Progress && $invoice->compte_prorata_pct == 0) {
            $invoice->compte_prorata_pct = TenantConfigService::get($tenant, 'commerce.invoices.compte_prorata_pct', 1.00);
        }

        if (empty($invoice->reference)) {
            $prefix = $invoice->type->value === 'situation' ? 'SIT' : 'FAC';
            $year = date('Y');
            $count = Invoices::whereYear('created_at', $year)->count() + 1;
            $invoice->reference = "{$prefix}-{$year}-".str_pad($count, 5, '0', STR_PAD_LEFT);
        }

        if (empty($invoice->due_date)) {
            $days = TenantConfigService::get($tenant, 'commerce.invoices.default_due_days', 30);
            $invoice->due_date = now()->addDays($days);
        }
    }

    public function saving(Invoices $invoice): void
    {
        // On s'assure que les montants de retenues sont synchronisés avec les % et le total_ttc
        if ($invoice->isDirty(['retenue_garantie_pct', 'compte_prorata_pct', 'total_ht', 'total_ttc'])) {
            $this->calculator->applyInvoicingSpecifics($invoice);
        }
    }
}
