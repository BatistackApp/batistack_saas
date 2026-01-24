<?php

namespace App\Services\Accounting;

use App\Enums\Accounting\EntryStatus;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;
use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use Illuminate\Support\Collection;

class EntryRecorderService
{
    public function __construct(private readonly SequenceService $sequenceService) {}

    /**
     * Crée une écriture comptable avec ses lignes
     *
     * @param  Collection<array{account_id: int, debit: float|string, credit: float|string, description?: string, analytical_code?: string}>  $lines
     */
    public function record(
        Tenant $tenant,
        AccountingJournal $journal,
        string $description,
        Collection $lines,
        ?\DateTime $postedAt = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
    ): AccountingEntry {
        $postedAt ??= now();

        $this->validateLines($lines);
        $this->validateBalance($lines);

        $reference = $this->sequenceService->generateReference($tenant, $journal, $postedAt);

        $entry = AccountingEntry::create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'reference' => $reference,
            'posted_at' => $postedAt,
            'description' => $description,
            'status' => EntryStatus::Draft,
            'total_debit' => $this->calculateDebit($lines),
            'total_credit' => $this->calculateCredit($lines),
            'source_type' => $sourceType,
            'source_id' => $sourceId,
        ]);

        foreach ($lines as $line) {
            AccountingEntryLine::create([
                'accounting_entry_id' => $entry->id,
                'accounting_accounts_id' => $line['account_id'],
                'debit' => $line['debit'] ?? 0,
                'credit' => $line['credit'] ?? 0,
                'description' => $line['description'] ?? null,
                'analytical_code' => $line['analytical_code'] ?? null,
            ]);
        }

        return $entry->load('lines.account');
    }

    /**
     * Poste une écriture brouillon (la rend définitive)
     */
    public function post(AccountingEntry $entry): AccountingEntry
    {
        if ($entry->status !== EntryStatus::Draft) {
            throw new \InvalidArgumentException('Seules les écritures en brouillon peuvent être postées.');
        }

        $entry->update(['status' => EntryStatus::Posted]);

        return $entry;
    }

    /**
     * Vérifie que chaque ligne a un compte valide
     */
    private function validateLines(Collection $lines): void
    {
        if ($lines->isEmpty()) {
            throw new \InvalidArgumentException('Une écriture doit contenir au moins une ligne.');
        }

        foreach ($lines as $line) {
            if (! isset($line['account_id'])) {
                throw new \InvalidArgumentException('Chaque ligne doit avoir un account_id.');
            }

            if ((! isset($line['debit']) || ! isset($line['credit'])) ||
                (empty($line['debit']) && empty($line['credit']))) {
                throw new \InvalidArgumentException('Chaque ligne doit avoir soit un débit soit un crédit.');
            }
        }
    }

    /**
     * Vérifie que l'écriture est équilibrée
     */
    private function validateBalance(Collection $lines): void
    {
        $totalDebit = $this->calculateDebit($lines);
        $totalCredit = $this->calculateCredit($lines);

        if (bccomp($totalDebit, $totalCredit, 2) !== 0) {
            throw new \InvalidArgumentException(
                "L'écriture n'est pas équilibrée : Débit {$totalDebit} ≠ Crédit {$totalCredit}"
            );
        }
    }

    private function calculateDebit(Collection $lines): string
    {
        return $lines->sum(fn ($line) => $line['debit'] ?? 0);
    }

    private function calculateCredit(Collection $lines): string
    {
        return $lines->sum(fn ($line) => $line['credit'] ?? 0);
    }
}
