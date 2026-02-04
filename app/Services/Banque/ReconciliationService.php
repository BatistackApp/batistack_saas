<?php

namespace App\Services\Banque;

use App\Enums\Banque\BankPaymentMethod;
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
        $amount = abs($transaction->amount);
        $label = mb_strtolower($transaction->label);

        $invoices = Invoices::where('tenants_id', $transaction->tenants_id)
            ->whereIn('status', [InvoiceStatus::Validated, InvoiceStatus::PartiallyPaid])
            ->with(['customer'])
            ->get();

        foreach ($invoices as $invoice) {
            $score = 0;
            $netToPay = round($invoice->net_to_pay, 2);

            if (abs($netToPay - $amount) <= 0.01) {
                $score += 60;
                if (str_contains($label, mb_strtolower($invoice->reference))) {
                    $score += 40;
                }
                if ($score < 100 && str_contains($label, mb_strtolower($invoice->customer->name))) {
                    $score += 20;
                }

                $suggestions[] = [
                    'invoice' => $invoice,
                    'score' => min($score, 100),
                    'reason' => $this->getMatchingReason($score),
                ];
            }
        }

        return collect($suggestions)->sortByDesc('score')->values()->all();
    }

    public function reconcile(BankTransaction $transaction, Invoices $invoice, float $amount): Payment
    {
        return DB::transaction(function () use ($transaction, $invoice, $amount) {
            $payment = Payment::create([
                'tenants_id' => $transaction->tenants_id,
                'bank_transaction_id' => $transaction->id,
                'invoice_id' => $invoice->id,
                'amount' => $amount,
                'payment_date' => $transaction->value_date,
                'method' => $this->guessMethod($transaction->label),
                'created_by' => Auth::id(),
            ]);

            $totalPaid = round($invoice->payments()->sum('amount'), 2);
            $target = round($invoice->net_to_pay, 2);

            $invoice->update([
                'status' => ($totalPaid >= $target) ? InvoiceStatus::Paid : InvoiceStatus::PartiallyPaid,
            ]);

            $transaction->update(['is_reconciled' => true]);

            return $payment;
        });
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
        if (str_contains($label, 'transfer_incoming')) {
            return BankPaymentMethod::TransferIncoming;
        }
        if (str_contains($label, 'transfer_outgoing')) {
            return BankPaymentMethod::TransferOutgoing;
        }
        if (str_contains($label, 'check')) {
            return BankPaymentMethod::Check;
        }
        if (str_contains($label, 'card')) {
            return BankPaymentMethod::Card;
        }
        if (str_contains($label, 'lcr')) {
            return BankPaymentMethod::LCR;
        }

        return BankPaymentMethod::TransferIncoming;
    }
}
