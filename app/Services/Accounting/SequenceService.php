<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingSequence;
use App\Models\Core\Tenant;
use Carbon\Carbon;
use Carbon\CarbonImmutable;

class SequenceService
{
    /**
     * Génère une référence séquentielle unique
     * Format : {CODE_JOURNAL}{ANNÉE}{NUMÉRO}
     * Exemple : VT20240001
     */
    public function generateReference(Tenant $tenant, AccountingJournal $journal, CarbonImmutable $date): string
    {
        $year = $date->year;

        $sequence = AccountingSequence::firstOrCreate(
            [
                'tenant_id' => $tenant->id,
                'accounting_journal_id' => $journal->id,
                'year' => $year,
            ],
            ['next_number' => 1]
        );

        $number = str_pad($sequence->next_number, 4, '0', STR_PAD_LEFT);
        $reference = "{$journal->code}{$year}{$number}";

        $sequence->increment('next_number');

        return $reference;
    }

    /**
     * Réinitialise les séquences pour une nouvelle année
     */
    public function resetForNewYear(Tenant $tenant, int $year): void
    {
        AccountingSequence::where('tenant_id', $tenant->id)
            ->where('year', $year)
            ->delete();
    }
}
