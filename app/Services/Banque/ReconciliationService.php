<?php

namespace App\Services\Banque;

use App\Enums\Banque\BankPaymentMethod;
use App\Enums\Banque\BankTransactionType;
use App\Enums\Commerce\InvoiceStatus;
use App\Models\Banque\BankTransaction;
use App\Models\Banque\Payment;
use App\Models\Commerce\Invoices;
use DB;
use Illuminate\Support\Facades\Auth;

class ReconciliationService
{
    public function suggestMatches(BankTransaction $transaction): array
    {
        $suggestions = [];
        $amount = abs((float) $transaction->amount);
        $label = mb_strtolower($transaction->label);
        $tenantId = $transaction->tenants_id;

        // 1. Si Crédit (Recette) -> On cherche des factures clients
        if ($transaction->type === BankTransactionType::Credit) {
            $suggestions = $this->searchInvoices($tenantId, $amount, $label);
        }
        // 2. Si Débit (Dépense) -> On cherche des factures fournisseurs
        else {
            $suggestions = $this->searchSupplierInvoices($tenantId, $amount, $label);
        }

        return collect($suggestions)->sortByDesc('score')->values()->all();
    }

    public function reconcile(BankTransaction $transaction, Invoices $invoice, float $amount): Payment
    {
        return DB::transaction(function () use ($transaction, $model, $amount) {

            $isSupplier = ($model instanceof SupplierInvoice) || (isset($model->supplier_id));

            $payment = Payment::create([
                'tenants_id' => $transaction->tenants_id,
                'bank_transaction_id' => $transaction->id,
                // On utilise le polymorphisme ou des colonnes séparées selon ton schéma Payment
                'invoice_id' => !$isSupplier ? $model->id : null,
                'supplier_invoice_id' => $isSupplier ? $model->id : null,
                'amount' => $amount,
                'payment_date' => $transaction->value_date,
                'method' => $this->guessMethod($transaction->label),
                'created_by' => Auth::id(),
            ]);

            // Mise à jour du statut du modèle (Client ou Fournisseur)
            $this->updateModelStatus($model);

            $transaction->update(['is_reconciled' => true]);

            return $payment;
        });
    }

    protected function updateModelStatus($model): void
    {
        // Logique de calcul du reste à payer simplifiée
        $totalPaid = round($model->payments()->sum('amount'), 2);
        $target = round($model->net_to_pay ?? $model->total_ttc, 2);

        $status = ($totalPaid >= $target) ? 'paid' : 'partially_paid';

        // Adaptation aux enums si nécessaire
        $model->update(['status' => $status]);
    }

    protected function getMatchingReason(int $score): string
    {
        return match (true) {
            $score >= 100 => 'Match parfait (Montant & Référence)',
            $score >= 80 => 'Match probable (Montant & Tiers)',
            default => 'Montant identique uniquement'
        };
    }

    protected function guessMethod(string $label): BankPaymentMethod
    {
        $label = mb_strtolower($label);
        if (str_contains($label, 'virement') || str_contains($label, 'transfer')) return BankPaymentMethod::TransferIncoming;
        if (str_contains($label, 'chèque') || str_contains($label, 'check')) return BankPaymentMethod::Check;
        if (str_contains($label, 'cb ') || str_contains($label, 'card')) return BankPaymentMethod::Card;

        return BankPaymentMethod::TransferIncoming;
    }

    /**
     * Recherche des factures clients (Recettes).
     */
    protected function searchInvoices(int $tenantId, float $amount, string $label): array
    {
        $suggestions = [];
        $invoices = Invoices::where('tenants_id', $tenantId)
            ->whereIn('status', [InvoiceStatus::Validated, InvoiceStatus::PartiallyPaid])
            ->with(['tiers'])
            ->get();

        foreach ($invoices as $invoice) {
            $score = $this->calculateScore($invoice, $amount, $label);
            if ($score > 0) {
                $suggestions[] = [
                    'type' => 'customer_invoice',
                    'model' => $invoice,
                    'score' => $score,
                    'reason' => $this->getMatchingReason($score)
                ];
            }
        }
        return $suggestions;
    }

    /**
     * Recherche des factures fournisseurs (Dépenses).
     */
    protected function searchSupplierInvoices(int $tenantId, float $amount, string $label): array
    {
        $suggestions = [];

        // On assume que SupplierInvoice a une structure similaire à Invoices
        $invoices = DB::table('supplier_invoices')
            ->where('tenants_id', $tenantId)
            ->whereIn('status', ['validated', 'partially_paid'])
            ->get();

        foreach ($invoices as $invoice) {
            $score = $this->calculateScore($invoice, $amount, $label);
            if ($score > 0) {
                $suggestions[] = [
                    'type' => 'supplier_invoice',
                    'model' => $invoice,
                    'score' => $score,
                    'reason' => $this->getMatchingReason($score)
                ];
            }
        }
        return $suggestions;
    }

    /**
     * Calcul de score générique.
     */
    protected function calculateScore($invoice, float $amount, string $label): int
    {
        $score = 0;
        $netToPay = round((float)($invoice->net_to_pay ?? $invoice->total_ttc), 2);

        if (abs($netToPay - $amount) <= 0.01) {
            $score += 60;
            if (str_contains($label, mb_strtolower($invoice->reference))) {
                $score += 40;
            }
            // Recherche du nom du tiers si disponible
            $tiersName = $invoice->tiers->name ?? ($invoice->supplier_name ?? '');
            if ($score < 100 && !empty($tiersName) && str_contains($label, mb_strtolower($tiersName))) {
                $score += 20;
            }
        }

        return min($score, 100);
    }
}
