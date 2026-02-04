<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\InvoiceStatus;
use App\Enums\Commerce\InvoiceType;
use App\Models\Commerce\InvoiceItem;
use App\Models\Commerce\Invoices;
use App\Models\Commerce\Quote;
use App\Models\Commerce\QuoteItem;
use App\Services\Core\DocumentManagementService;
use DB;
use Exception;
use Log;

class InvoicingService
{
    public function __construct(protected FinancialCalculatorService $calculator, protected DocumentManagementService $document) {}

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
                }
            }

            // 3. Application des calculs finaux (Taxes, RG, Prorata)
            $this->calculator->updateDocumentTotals($invoice);

            return $invoice;
        });
    }

    /**
     * VALIDE ET SCELLE LA FACTURE (Action irréversible)
     * Cette méthode centralise le passage au statut définitif.
     */
    public function validateInvoice(Invoices $invoice): Invoices
    {
        if ($invoice->status !== InvoiceStatus::Draft) {
            throw new Exception('Seule une facture en brouillon peut être validée.');
        }

        return DB::transaction(function () use ($invoice) {
            // 1. Scellement du numéro de facture (Séquence légale)
            $invoice->reference = $this->generateFinalReference($invoice);

            // 2. Mise à jour du statut
            $invoice->status = InvoiceStatus::Validated;
            $invoice->save();

            // 3. Génération du PDF (Placeholder pour l'étape future)
            $this->document->generatePdf($invoice, 'pdf.commerce.invoices', 'invoices');

            Log::info("Facture scellée : {$invoice->reference} pour le client ID {$invoice->tiers_id}");

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
     * LIBÉRATION DE LA RETENUE DE GARANTIE
     *
     * @param  string  $reportPath  Chemin du PV de réception ou de l'attestation
     */
    public function releaseRetenueGarantie(Invoices $invoice, string $reportPath): Invoices
    {
        if ($invoice->is_retenue_garantie_released) {
            throw new Exception('La retenue de garantie a déjà été libérée pour cette facture.');
        }

        if ($invoice->status !== InvoiceStatus::Paid && $invoice->status !== InvoiceStatus::Validated) {
            throw new Exception('La facture doit être validée ou payée (hors RG) pour libérer la garantie.');
        }

        return DB::transaction(function () use ($invoice, $reportPath) {
            $invoice->update([
                'is_retenue_garantie_released' => true,
                'retenue_garantie_released_at' => now(),
                'reception_report_path' => $reportPath,
            ]);

            // Si la facture était "Payée" (sous-entendu le net à payer),
            // on peut la laisser en "Partially Paid" jusqu'à réception de la RG
            // ou créer une écriture de règlement spécifique.

            return $invoice;
        });
    }

    /**
     * Définit la date théorique de libération (ex: réception + 12 mois).
     */
    public function setTheoreticalReleaseDate(Invoices $invoice, \DateTime $receptionDate): void
    {
        $invoice->update([
            'retenue_garantie_release_date' => $receptionDate->modify('+1 year')
        ]);
    }

    /**
     * Génère une référence définitive basée sur l'année et le type.
     */
    protected function generateFinalReference(Invoices $invoice): string
    {
        $prefix = $invoice->type === InvoiceType::Progress ? 'SIT' : 'FAC';
        $year = now()->year;

        // On cherche le dernier numéro pour l'année en cours
        $lastInvoice = Invoices::where('reference', 'LIKE', "{$prefix}-{$year}-%")
            ->where('status', '!=', InvoiceStatus::Draft)
            ->orderBy('reference', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->reference, -5);
            $nextNumber = $lastNumber + 1;
        }

        return "{$prefix}-{$year}-".str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}
