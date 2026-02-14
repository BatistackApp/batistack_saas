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
        $totalDebit = $entry->lines()->sum('debit');
        $totalCredit = $entry->lines()->sum('credit');
        $entry->updateQuietly([
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
        ]);
    }
}
