<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\Journal;
use Carbon\Carbon;
use League\Csv\Writer;
use Storage;

class FecExportService
{
    /**
     * Génère un fichier FEC conforme DGFIP.
     * Format CSV avec 13 colonnes obligatoires.
     */
    public function export(Carbon $startDate, Carbon $endDate, ?string $disk = 'public'): string
    {
        $entries = AccountingEntry::where('status', 'validated')
            ->whereBetween('accounting_date', [$startDate, $endDate])
            ->with(['journal', 'lines.account'])
            ->orderBy('reference_number')
            ->get();

        $csv = Writer::createFromString('');

        // En-têtes obligatoires FEC
        $csv->insertOne([
            'JournalCode',
            'JournalLib',
            'EcritureNum',
            'EcritureDate',
            'CompteNum',
            'CompteLib',
            'CompAuxNum',
            'CompAuxLib',
            'PieceRef',
            'PieceDate',
            'EcritureLib',
            'Debit',
            'Credit', ]);

        // Ajouter les lignes d'écritures
        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                $csv->insertOne([
                    $entry->journal->code,                    // JournalCode
                    $entry->journal->label,                   // JournalLib
                    $entry->reference_number,                 // EcritureNum
                    $entry->accounting_date->format('Ymd'),   // EcritureDate
                    $line->account->account_number,           // CompteNum
                    $line->account->account_label,            // CompteLib
                    '',                                       // CompAuxNum
                    '',                                       // CompAuxLib
                    $entry->reference_number,                 // PieceRef
                    $entry->accounting_date->format('Ymd'),   // PieceDate
                    $line->description ?? $entry->label,      // EcritureLib
                    $line->debit > 0 ? (string) $line->debit : '',    // Debit
                    $line->credit > 0 ? (string) $line->credit : '',   // Credit
                ]);
            }
        }

        // Sauvegarder le fichier
        $fileName = sprintf(
            'fec_export_%s_to_%s.csv',
            $startDate->format('Ymd'),
            $endDate->format('Ymd')
        );

        $filePath = "accounting/fec/{$fileName}";
        Storage::disk($disk)->put($filePath, $csv->getContent());

        return $filePath;
    }

    /**
     * Valide un export FEC avant transmission à l'administration.
     */
    public function validate(Carbon $startDate, Carbon $endDate): array
    {
        $entries = AccountingEntry::where('status', 'validated')
            ->whereBetween('accounting_date', [$startDate, $endDate])
            ->with('lines')
            ->get();

        $errors = [];

        // Vérifier l'équilibre global
        $totalDebit = $entries->sum(fn ($e) => $e->total_debit);
        $totalCredit = $entries->sum(fn ($e) => $e->total_credit);

        if (bccomp((string) $totalDebit, (string) $totalCredit, 4) !== 0) {
            $errors[] = "Équilibre global non respecté: Débits {$totalDebit} ≠ Crédits {$totalCredit}";
        }

        // Vérifier la continuité de numérotation par journal
        $journals = Journal::where('is_active', true)->get();

        foreach ($journals as $journal) {
            $journalEntries = $entries->where('journal_id', $journal->id);

            if ($journalEntries->isNotEmpty()) {
                $generator = app(SequenceNumberGenerator::class);
                $gaps = $generator->validateSequenceIntegrity($journal, $startDate, $endDate);

                if (! empty($gaps)) {
                    foreach ($gaps as $gap) {
                        $errors[] = "Trou de séquence détecté dans {$journal->code}: Attendu {$gap['expected']}, reçu {$gap['received']}";
                    }
                }
            }
        }

        return $errors;
    }
}
