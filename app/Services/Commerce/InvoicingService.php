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

            $invoice = Invoices::create([
                'tenants_id' => $quote->tenants_id,
                'tiers_id' => $quote->customer_id,
                'project_id' => $quote->project_id,
                'quote_id' => $quote->id,
                'type' => InvoiceType::Progress,
                'reference' => 'TEMP-'.uniqid(),
                'situation_number' => $situationNumber,
                'status' => InvoiceStatus::Draft,
            ]);

            foreach ($progressData as $data) {
                $quoteItem = QuoteItem::findOrFail($data['quote_item_id']);
                $newTotalProgress = (float) $data['progress_percentage'];

                // Le montant déjà facturé inclura les avoirs éventuels
                $alreadyInvoicedHt = $this->getAmountInvoicedToDate($quoteItem, $invoice->id);

                $targetCumulativeHt = ($quoteItem->unit_price_ht * $quoteItem->quantity) * ($newTotalProgress / 100);
                $amountPeriodHt = $targetCumulativeHt - $alreadyInvoicedHt;

                if (round($amountPeriodHt, 2) != 0) {
                    InvoiceItem::create([
                        'invoices_id' => $invoice->id,
                        'quote_item_id' => $quoteItem->id,
                        'label' => $quoteItem->label,
                        'quantity' => 1,
                        'unit_price_ht' => $amountPeriodHt,
                        'tax_rate' => $quoteItem->tax_rate ?? 20.00,
                        'progress_percentage' => $newTotalProgress,
                    ]);
                }
            }

            $this->calculator->updateDocumentTotals($invoice);

            return $invoice;
        });
    }

    /**
     * GÉNÉRATION D'UN AVOIR POUR SITUATION
     * Crée un document qui annule financièrement une situation validée
     * et réinitialise les droits à facturer sur le devis.
     */
    public function createCreditNoteFromSituation(Invoices $originalInvoice): Invoices
    {
        if ($originalInvoice->status === InvoiceStatus::Draft) {
            throw new Exception('Impossible de créer un avoir pour une facture en brouillon. Supprimez-la simplement.');
        }

        if ($originalInvoice->type !== InvoiceType::Progress) {
            throw new Exception('Ce workflow est réservé aux situations de travaux.');
        }

        return DB::transaction(function () use ($originalInvoice) {
            // 1. Création de l'en-tête de l'Avoir
            $creditNote = Invoices::create([
                'tenants_id' => $originalInvoice->tenants_id,
                'tiers_id' => $originalInvoice->tiers_id,
                'project_id' => $originalInvoice->project_id,
                'quote_id' => $originalInvoice->quote_id,
                'type' => InvoiceType::CreditNote,
                'reference' => 'AVO-'.$originalInvoice->reference,
                'situation_number' => $originalInvoice->situation_number,
                'status' => InvoiceStatus::Draft,
                'due_date' => now(),
                'notes' => "Avoir annulant et remplaçant la situation n°{$originalInvoice->situation_number} ({$originalInvoice->reference})",
                'retenue_garantie_pct' => $originalInvoice->retenue_garantie_pct,
                'compte_prorata_pct' => $originalInvoice->compte_prorata_pct,
            ]);

            // 2. Création des lignes négatives (Miroir)
            foreach ($originalInvoice->items as $item) {
                InvoiceItem::create([
                    'invoices_id' => $creditNote->id,
                    'quote_item_id' => $item->quote_item_id,
                    'label' => 'Annulation : '.$item->label,
                    'quantity' => $item->quantity,
                    'unit_price_ht' => -$item->unit_price_ht, // Inversion du signe
                    'tax_rate' => $item->tax_rate,
                    'progress_percentage' => 0, // On remet l'avancement cumulé à zéro pour cette pièce
                ]);
            }

            // 3. Calcul des totaux via le service centralisé
            $this->calculator->updateDocumentTotals($creditNote);

            return $creditNote;
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
        // La somme algébrique gère nativement les avoirs (positif + négatif = 0)
        return (float) InvoiceItem::where('quote_item_id', $quoteItem->id)
            ->whereHas('invoices', function ($q) use ($excludeInvoiceId) {
                $q->whereIn('status', [InvoiceStatus::Validated, InvoiceStatus::Paid, InvoiceStatus::PartiallyPaid])
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
            'retenue_garantie_release_date' => $receptionDate->modify('+1 year'),
        ]);
    }

    /**
     * Génère une référence définitive basée sur l'année et le type.
     */
    protected function generateFinalReference(Invoices $invoice): string
    {
        $prefix = match ($invoice->type) {
            InvoiceType::Progress => 'SIT',
            InvoiceType::CreditNote => 'AVO',
            default => 'FAC'
        };
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
