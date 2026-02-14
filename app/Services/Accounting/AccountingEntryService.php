<?php

namespace App\Services\Accounting;

use App\Enums\Accounting\EntryStatus;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;
use App\Models\Accounting\ChartOfAccount;
use App\Models\Accounting\Journal;
use Carbon\Carbon;
use DB;

class AccountingEntryService
{
    public function __construct(
        private SequenceNumberGenerator $sequenceGenerator,
        private EntryBalanceValidator $validator,
        private BalanceCalculator $balanceCalculator,
    ) {}

    /**
     * Crée une écriture comptable complète avec validation.
     */
    public function create(
        Journal $journal,
        Carbon $accountingDate,
        string $label,
        array $lines,
        ?string $description = null,
        ?int $createdBy = null,
    ): AccountingEntry {
        return DB::transaction(function () use ($journal, $accountingDate, $label, $lines, $description, $createdBy) {
            // Générer le numéro séquentiel
            $referenceNumber = $this->sequenceGenerator->generate($journal, $accountingDate);

            // Créer l'en-tête de l'écriture
            $entry = AccountingEntry::create([
                'journal_id' => $journal->id,
                'reference_number' => $referenceNumber,
                'accounting_date' => $accountingDate,
                'label' => $label,
                'description' => $description,
                'status' => EntryStatus::Draft,
                'total_debit' => 0,
                'total_credit' => 0,
                'created_by' => $createdBy,
            ]);

            // Ajouter les lignes d'écriture
            $totalDebit = 0;
            $totalCredit = 0;
            $lineOrder = 0;

            foreach ($lines as $lineData) {
                $account = ChartOfAccount::findOrFail($lineData['chart_of_account_id']);

                $debit = isset($lineData['debit']) ? bcadd('0', (string) $lineData['debit'], 4) : '0';
                $credit = isset($lineData['credit']) ? bcadd('0', (string) $lineData['credit'], 4) : '0';

                AccountingEntryLine::create([
                    'accounting_entry_id' => $entry->id,
                    'chart_of_account_id' => $account->id,
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $lineData['description'] ?? null,
                    'line_order' => $lineOrder++,
                ]);

                $totalDebit = bcadd($totalDebit, $debit, 4);
                $totalCredit = bcadd($totalCredit, $credit, 4);
            }

            // Mettre à jour les totaux
            $entry->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Valider l'équilibre
            $this->validator->validate($entry);
            $this->validator->validateLines($entry);

            return $entry;
        });
    }

    /**
     * Valide une écriture (passage en statut "validated").
     * Une fois validée, l'écriture devient immuable.
     */
    public function validate(AccountingEntry $entry): AccountingEntry
    {
        if ($entry->status === EntryStatus::Validated) {
            throw new \RuntimeException(
                "L'écriture {$entry->reference_number} est déjà validée."
            );
        }

        if ($entry->status === EntryStatus::Closed || $entry->status === EntryStatus::Cancelled) {
            throw new \RuntimeException(
                "L'écriture {$entry->reference_number} ne peut pas être validée (statut: {$entry->status->value})."
            );
        }

        return DB::transaction(function () use ($entry) {
            $this->validator->validate($entry);
            $this->validator->validateLines($entry);

            $entry->update([
                'status' => EntryStatus::Validated,
                'validated_at' => now(),
                'validated_by' => auth()->id(),
            ]);

            // Invalider le cache des soldes pour tous les comptes concernés
            foreach ($entry->lines as $line) {
                $this->balanceCalculator->invalidateCache($line->account, $entry->accounting_date);
            }

            return $entry;
        });
    }

    /**
     * Annule une écriture (passage en statut "cancelled").
     * Seules les écritures en brouillon peuvent être annulées.
     */
    public function cancel(AccountingEntry $entry): AccountingEntry
    {
        if ($entry->status !== EntryStatus::Draft) {
            throw new \RuntimeException(
                "Seules les écritures en brouillon peuvent être annulées."
            );
        }

        return $entry->update(['status' => EntryStatus::Cancelled]) ? $entry->refresh() : $entry;
    }

    /**
     * Récupère le solde actuel d'un compte.
     */
    public function getAccountBalance(ChartOfAccount $account, ?Carbon $asOf = null): string
    {
        return $this->balanceCalculator->calculate($account, $asOf);
    }
}
