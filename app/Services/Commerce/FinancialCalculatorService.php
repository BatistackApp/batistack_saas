<?php

namespace App\Services\Commerce;

use App\Models\Commerce\Invoices;
use App\Models\Commerce\PurchaseOrder;
use App\Models\Commerce\Quote;
use App\Services\Core\TenantConfigService;
use DB;
use Illuminate\Database\Eloquent\Model;

class FinancialCalculatorService
{
    /**
     * Recalcule et met à jour les totaux d'un document (Devis, Commande ou Facture).
     */
    public function updateDocumentTotals(Model $document): void
    {
        $itemTable = $this->getItemTable($document);
        $foreignKey = $this->getForeignKey($document);

        // 1. Calcul des sommes HT et TVA par ligne (pour gérer les taux multiples)
        $totals = DB::table($itemTable)
            ->where($foreignKey, $document->id)
            ->selectRaw('
                SUM(quantity * unit_price_ht) as ht,
                SUM(quantity * unit_price_ht * (tax_rate / 100)) as tva
            ')->first();

        $totalHt = round((float) ($totals->ht ?? 0), 2);
        $totalTva = round((float) ($totals->tva ?? 0), 2);
        $totalTtc = $totalHt + $totalTva;

        // 2. Mise à jour de l'en-tête selon le type de modèle
        $updateData = [
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
        ];

        if ($document instanceof Quote || $document instanceof Invoices) {
            $updateData['total_ttc'] = $totalTtc;
        }

        $document->update($updateData);

        // 3. Application des spécificités BTP si c'est une facture
        if ($document instanceof Invoices) {
            $this->applyInvoicingSpecifics($document);
        }
    }

    /**
     * Calcule la retenue de garantie et le net à payer pour une facture/situation.
     */
    public function applyInvoicingSpecifics(Invoices $invoice): void
    {
        $tenant = $invoice->tenant;

        // Récupération du taux de RG (soit celui du doc, soit défaut tenant)
        $rgPct = $invoice->retenue_garantie_pct > 0
            ? $invoice->retenue_garantie_pct
            : TenantConfigService::get($tenant, 'commerce.invoices.retenue_garantie_pct', 5.00);

        $rgAmount = round($invoice->total_ttc * ($rgPct / 100), 2);

        $invoice->update([
            'retenue_garantie_amount' => $rgAmount,
            // Note: net_to_pay est un accesseur dans le modèle, pas besoin de le persister ici
        ]);
    }

    /**
     * Détermine la table des items associée au document.
     */
    protected function getItemTable(Model $document): string
    {
        return match (true) {
            $document instanceof Quote => 'quote_items',
            $document instanceof Invoices => 'invoice_items',
            $document instanceof PurchaseOrder => 'purchase_order_items',
            default => throw new \Exception("Modèle non supporté pour le calcul financier."),
        };
    }

    /**
     * Détermine la clé étrangère pour le lien parent-enfant.
     */
    protected function getForeignKey(Model $document): string
    {
        return match (true) {
            $document instanceof Quote => 'quote_id',
            $document instanceof Invoices => 'invoice_id',
            $document instanceof PurchaseOrder => 'purchase_order_id',
            default => throw new \Exception("Clé étrangère inconnue."),
        };
    }
}
