<?php

namespace App\Observers\Accounting;

use App\Enums\Accounting\EntryStatus;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\PeriodClosure;
use App\Services\Accounting\PeriodClosureService;

class AccountingEntryObserver
{
    public function __construct(protected PeriodClosureService $closureService) {}
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

    /**
     * Empêche toute création/modification si la période est clôturée.
     */
    public function saving(AccountingEntry $entry): void
    {
        if ($this->closureService->isPeriodClosed($entry->accounting_date)) {
            throw new \RuntimeException("Action impossible : La période comptable est clôturée.");
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

    /**
     * Bloque la modification des écritures validées (Immuabilité).
     */
    public function updating(AccountingEntry $entry): void
    {
        if ($entry->status === EntryStatus::Validated && !$entry->isDirty('status')) {
            throw new \RuntimeException("Une écriture validée ne peut plus être modifiée.");
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
