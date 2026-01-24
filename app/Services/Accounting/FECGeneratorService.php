<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingEntry;
use App\Models\Core\Tenant;
use Carbon\Carbon;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;

class FECGeneratorService
{
    /**
     * Génère un fichier FEC (Fichier d'Échanges Comptables) au format CSV
     * Format légal français
     */
    public function generate(
        Tenant $tenant,
        Carbon $startDate,
        Carbon $endDate,
    ): string {
        $entries = AccountingEntry::query()
            ->where('tenant_id', $tenant->id)
            ->whereBetween('posted_at', [$startDate, $endDate])
            ->whereIn('status', ['posted', 'locked'])
            ->with('journal', 'lines.account')
            ->orderBy('posted_at')
            ->orderBy('reference')
            ->get();

        $csv = Writer::createFromString('');
        $csv->insertOne([
            'JournalCode',
            'JournalLib',
            'EcritureNum',
            'EcritureDate',
            'CompteNum',
            'CompteLib',
            'CompteAux',
            'EcritureAuxNum',
            'EcritureDebit',
            'EcritureCredit',
            'EcritureLibelle',
            'EcritureLettrage',
            'DateLettrage',
            'ValidDate',
            'Montantdevise',
            'Idevise',
        ]);

        foreach ($entries as $entry) {
            foreach ($entry->lines as $line) {
                $csv->insertOne([
                    'JournalCode' => $entry->journal->code,
                    'JournalLib' => $entry->journal->name,
                    'EcritureNum' => $entry->reference,
                    'EcritureDate' => $entry->posted_at->format('Ymd'),
                    'CompteNum' => $line->account->number,
                    'CompteLib' => $line->account->name,
                    'CompteAux' => '',
                    'EcritureAuxNum' => '',
                    'EcritureDebit' => number_format((float) $line->debit, 2, '.', ''),
                    'EcritureCredit' => number_format((float) $line->credit, 2, '.', ''),
                    'EcritureLibelle' => $line->description ?? $entry->description,
                    'EcritureLettrage' => '',
                    'DateLettrage' => '',
                    'ValidDate' => $entry->posted_at->format('Ymd'),
                    'Montantdevise' => '',
                    'Idevise' => 'EUR',
                ]);
            }
        }

        return $csv->getContent();
    }
}
