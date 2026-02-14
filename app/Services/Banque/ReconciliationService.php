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
        $amount = (string) abs($transaction->amount);
        $label = mb_strtolower($transaction->label);
        $tenantId = $transaction->tenants_id;

        // On définit le type de tiers recherché selon le sens de la transaction
        $targetType = ($transaction->type === BankTransactionType::Credit)
            ? 'client' // Recette -> On cherche un client
            : 'fournisseur'; // Dépense -> On cherche un fournisseur (ou sous-traitant)

        $suggestions = $this->searchInvoices($tenantId, $amount, $label, $targetType);

        return collect($suggestions)->sortByDesc('score')->values()->all();
    }

    public function reconcile(BankTransaction $transaction, Invoices $model, float $amount): Payment
    {
        return DB::transaction(function () use ($transaction, $model, $amount) {

            $payment = Payment::create([
                'tenants_id' => $transaction->tenants_id,
                'bank_transaction_id' => $transaction->id,
                'invoice_id' => $model->id,
                'amount' => $amount,
                'payment_date' => $transaction->value_date,
                'method' => $this->guessMethod($transaction->label, $transaction->type),
                'created_by' => auth()->user()->id,
            ]);

            $this->updateInvoiceStatus($model);
            $transaction->update(['is_reconciled' => true]);

            return $payment;
        });
    }

    protected function updateInvoiceStatus(Invoices $invoice): void
    {
        $totalPaid = (string) $invoice->payments()->sum('amount');
        $target = (string) $invoice->net_to_pay;

        $status = (bccomp($totalPaid, $target, 2) >= 0) ? InvoiceStatus::Paid : InvoiceStatus::PartiallyPaid;
        $invoice->update(['status' => $status]);
    }

    protected function getMatchingReason(int $score): string
    {
        return match (true) {
            $score >= 100 => 'Match parfait (Montant & Référence)',
            $score >= 80 => 'Match probable (Montant & Tiers)',
            default => 'Montant identique uniquement'
        };
    }

    protected function guessMethod(string $label, BankTransactionType $type): BankPaymentMethod
    {
        $label = mb_strtolower($label);

        if (str_contains($label, 'chèque') || str_contains($label, 'check')) {
            return BankPaymentMethod::Check;
        }
        if (str_contains($label, 'cb ') || str_contains($label, 'card')) {
            return BankPaymentMethod::Card;
        }
        // Ajouter d'autres méthodes spécifiques avant les virements généraux si nécessaire (e.g., LCR)

        // Gestion des virements en fonction du type de transaction
        if (str_contains($label, 'virement') || str_contains($label, 'transfer')) {
            return ($type === BankTransactionType::Credit) ? BankPaymentMethod::TransferIncoming : BankPaymentMethod::TransferOutgoing;
        }

        // Par défaut, si rien n'est trouvé, on peut utiliser un virement par défaut
        // Il pourrait être judicieux de logguer cette situation ou de retourner une méthode 'Unknown' si l'Enum le permet.
        return ($type === BankTransactionType::Credit) ? BankPaymentMethod::TransferIncoming : BankPaymentMethod::TransferOutgoing;
    }

    /**
     * Recherche dans l'unique table Invoices filtrée par le type de tiers.
     */
    protected function searchInvoices(int $tenantId, string $amount, string $label, string $tierTypeCode): array
    {
        $suggestions = [];

        // Utilisation de whereHas pour filtrer sur la relation polymorphe/multiple des types de tiers
        $invoices = Invoices::where('tenants_id', $tenantId)
            ->whereIn('status', [InvoiceStatus::Validated, InvoiceStatus::PartiallyPaid])
            ->whereHas('tiers.types', function($query) use ($tierTypeCode) {
                $query->where('type', $tierTypeCode);
            })
            ->with(['tiers'])
            ->get();

        foreach ($invoices as $invoice) {
            $score = $this->calculateScore($invoice, $amount, $label);
            if ($score > 0) {
                $suggestions[] = [
                    'type' => ($tierTypeCode === 'client') ? 'sale' : 'purchase',
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
    protected function calculateScore($invoice, string $amount, string $label): int
    {
        $score = 0;

        // 1. Calculer le montant restant dû avec précision
        $paidAmount = (string) $invoice->payments()->sum('amount');
        $totalInvoiceAmount = (string) ($invoice->net_to_pay ?? $invoice->total_ttc); // Assumer net_to_pay est le total
        $remainingAmountDue = bcsub($totalInvoiceAmount, $paidAmount, 2);

        // 2. Comparer le montant de la transaction avec le montant restant dû
        // Utiliser bccomp pour comparer les chaînes avec une tolérance
        $diff = bcsub($remainingAmountDue, $amount, 4); // Calculer la différence avec haute précision
        // Vérifier si la différence absolue est dans une tolérance acceptable (ex: 0.01)
        if (bccomp(abs($diff), '0.01', 4) <= 0) { // bccomp(abs(diff), '0.01', precision)
            $score += 60;

            if (str_contains($label, mb_strtolower($invoice->reference))) {
                $score += 40;
            }

            $tiersName = $invoice->tiers->name ?? ($invoice->supplier->name ?? ''); // Améliorer cette logique si supplier existe séparément
            if ($score < 100 && !empty($tiersName) && str_contains($label, mb_strtolower($tiersName))) {
                $score += 20;
            }
        }

        return min($score, 100);
    }
}
