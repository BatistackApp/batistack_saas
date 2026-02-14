<?php

namespace App\Observers\Accounting;

use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\PeriodClosure;

class AccountingEntryObserver
{
    public function creating(AccountingEntry $entry): void
    {
        $periodClosure = PeriodClosure::where('month', $entry->accounting_date->month)
            ->where('year', $entry->accounting_date->year)
            ->first();

        if ($periodClosure?->is_locked) {
            throw new \RuntimeException(
                "Impossible d'ajouter une écriture : la période {$entry->accounting_date->format('m/Y')} est clôturée."
            );
        }
    }

    public function updated(AccountingEntry $entry): void
    {
        if ($entry->wasChanged('status') && $entry->status->value === 'validated') {
            foreach ($entry->lines as $line) {
                app(\App\Services\Accounting\BalanceCalculator::class)
                    ->invalidateCache($line->account, $entry->accounting_date);
            }
        }
    }

    public function deleting(AccountingEntry $entry): void
    {
        if ($entry->status->value === 'validated') {
            throw new \RuntimeException(
                "Impossible de supprimer une écriture validée. Veuillez l'annuler via un avoir."
            );
        }
    }
}
