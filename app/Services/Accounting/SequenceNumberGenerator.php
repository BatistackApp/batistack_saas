<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\Journal;
use Carbon\Carbon;
use DB;

class SequenceNumberGenerator
{
    /**
     * Génère un numéro séquentiel unique pour une écriture comptable.
     * Format: [CODE_JOURNAL]/[YYYYMMDD]/[INCREMENT_JOURNALIER]
     */
    public function generate(Journal $journal, ?Carbon $date = null): string
    {
        $date ??= today();
        $dateFormatted = $date->format('Ymd');
        $journalCode = $journal->code;

        return DB::transaction(function () use ($journal, $journalCode, $dateFormatted, $date) {
            // Récupérer le dernier incrément du jour pour ce journal
            $lastEntry = AccountingEntry::where('journal_id', $journal->id)
                ->whereDate('accounting_date', $date)
                ->orderBy('id', 'desc')
                ->first();

            $nextIncrement = 1;
            if ($lastEntry && preg_match('/\/(\d+)$/', $lastEntry->reference_number, $matches)) {
                $nextIncrement = (int) $matches[1] + 1;
            }

            $referenceNumber = sprintf(
                '%s/%s/%04d',
                $journalCode,
                $dateFormatted,
                $nextIncrement
            );

            // Vérifier l'unicité
            if (AccountingEntry::where('reference_number', $referenceNumber)->exists()) {
                throw new \RuntimeException(
                    "Collision de numérotation détectée pour {$referenceNumber}"
                );
            }

            return $referenceNumber;
        });
    }

    /**
     * Valide qu'il n'y a pas de trou dans la séquence d'un journal sur une période.
     */
    public function validateSequenceIntegrity(Journal $journal, Carbon $startDate, Carbon $endDate): array
    {
        $entries = AccountingEntry::where('journal_id', $journal->id)
            ->whereBetween('accounting_date', [$startDate, $endDate])
            ->orderBy('reference_number')
            ->get();

        $gaps = [];
        $previousNumber = 0;

        foreach ($entries as $entry) {
            if (preg_match('/\/(\d+)$/', $entry->reference_number, $matches)) {
                $currentNumber = (int) $matches[1];

                if ($previousNumber > 0 && $currentNumber !== $previousNumber + 1) {
                    $gaps[] = [
                        'expected' => $previousNumber + 1,
                        'received' => $currentNumber,
                        'last_valid' => $previousNumber,
                    ];
                }

                $previousNumber = $currentNumber;
            }
        }

        return $gaps;
    }
}
