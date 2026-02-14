<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingEntry;
use Illuminate\Validation\ValidationException;

class EntryBalanceValidator
{
    /**
     * Vérifie qu'une écriture est équilibrée (Débits = Crédits).
     */
    public function validate(AccountingEntry $entry): bool
    {
        $totalDebit = bcadd(
            '0',
            (string) $entry->total_debit,
            4
        );

        $totalCredit = bcadd(
            '0',
            (string) $entry->total_credit,
            4
        );

        if (bccomp($totalDebit, $totalCredit, 4) !== 0) {
            throw ValidationException::withMessages([
                'balance' => "L'écriture n'est pas équilibrée. Débits: {$totalDebit} ≠ Crédits: {$totalCredit}",
            ]);
        }

        return true;
    }

    /**
     * Valide que chaque ligne d'écriture est correcte.
     */
    public function validateLines(AccountingEntry $entry): bool
    {
        $lines = $entry->lines;

        if ($lines->isEmpty()) {
            throw ValidationException::withMessages([
                'lines' => "L'écriture doit contenir au moins 2 lignes.",
            ]);
        }

        foreach ($lines as $line) {
            if (bccomp($line->debit, '0', 4) < 0 || bccomp($line->credit, '0', 4) < 0) {
                throw ValidationException::withMessages([
                    'lines' => "Les montants doivent être positifs ou nuls.",
                ]);
            }

            // Une ligne ne peut pas avoir à la fois un débit et un crédit
            if (bccomp($line->debit, '0', 4) > 0 && bccomp($line->credit, '0', 4) > 0) {
                throw ValidationException::withMessages([
                    'lines' => "Une ligne ne peut pas avoir à la fois un débit et un crédit.",
                ]);
            }
        }

        return true;
    }
}
