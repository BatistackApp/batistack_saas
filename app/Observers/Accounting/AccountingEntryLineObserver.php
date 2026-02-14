<?php

namespace App\Observers\Accounting;

use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;

class AccountingEntryLineObserver
{
    public function saved(AccountingEntryLine $line): void
    {
        $this->syncEntryTotals($line->entry);
    }

    public function deleted(AccountingEntryLine $line): void
    {
        $this->syncEntryTotals($line->entry);
    }

    protected function syncEntryTotals(AccountingEntry $entry): void
    {
        $totals = $entry->lines()
            ->selectRaw('SUM(debit) as total_debit, SUM(credit) as total_credit')
            ->first();

        // On utilise updateQuietly pour ne pas redéclencher l'observer de l'entry
        $entry->updateQuietly([
            'total_debit' => $totals->total_debit ?? 0,
            'total_credit' => $totals->total_credit ?? 0,
        ]);
    }
}
