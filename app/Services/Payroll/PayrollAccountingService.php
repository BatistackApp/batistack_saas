<?php

namespace App\Services\Payroll;

use App\Enums\Accounting\EntryStatus;
use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;
use App\Models\Accounting\AccountingJournal;
use App\Models\Payroll\PayrollSlip;

class PayrollAccountingService
{
    public function createAccountingEntries(PayrollSlip $slip): AccountingEntry
    {
        $journal = AccountingJournal::firstWhere('code', 'PAIE');

        if (! $journal) {
            throw new \Exception('Journal PAIE non trouvé');
        }

        // Créer l'écriture comptable
        $entry = AccountingEntry::create([
            'tenant_id' => $journal->tenant_id,
            'accounting_journal_id' => $journal->id,
            'reference' => "{$slip->employee->employee_number}{$slip->year}{$slip->month}",
            'posted_at' => now(),
            'description' => "Paie - {$slip->employee->name} - {$slip->year}-{$slip->month}",
            'status' => EntryStatus::Draft,
            'total_debit' => $slip->gross_amount + $slip->transport_amount,
            'total_credit' => $slip->net_amount + $slip->social_contributions,
            'source_type' => PayrollSlip::class,
            'source_id' => $slip->id,
        ]);

        AccountingEntryLine::create([
            'accounting_entry_id' => $entry->id,
            'accounting_accounts_id' => AccountingAccounts::where('tenant_id', $journal->tenant_id)->where('number', 6411)->first()->id,
            'debit' => $slip->gross_amount,
            'credit' => 0,
            'description' => "Salaire Brut - {$slip->employee->last_name} {$slip->employee->first_name}"
        ]);

        AccountingEntryLine::create([
            'accounting_entry_id' => $entry->id,
            'accounting_accounts_id' => AccountingAccounts::where('tenant_id', $journal->tenant_id)->where('number', 4281)->first()->id,
            'debit' => 0,
            'credit' => $slip->net_amount,
            'description' => "Dettes salaires - {$slip->employee->last_name} {$slip->employee->first_name}"
        ]);

        AccountingEntryLine::create([
            'accounting_entry_id' => $entry->id,
            'accounting_accounts_id' => AccountingAccounts::where('tenant_id', $journal->tenant_id)->where('number', 4391)->first()->id,
            'debit' => 0,
            'credit' => $slip->social_contributions,
            'description' => "Charges sociales - {$slip->employee->last_name} {$slip->employee->first_name}"
        ]);

        if ($slip->transport_amount > 0) {
            AccountingEntryLine::create([
                'accounting_entry_id' => $entry->id,
                'accounting_accounts_id' => AccountingAccounts::where('tenant_id', $journal->tenant_id)->where('number', 6422)->first()->id,
                'debit' => $slip->transport_amount,
                'credit' => 0,
                'description' => "Indemnité Trajets - {$slip->employee->last_name} {$slip->employee->first_name}"
            ]);
        }

        return $entry;
    }

    public function reverseAccountingEntries(PayrollSlip $slip): void
    {
        // Trouver les écritures existantes
        $entries = AccountingEntry::query()
            ->where('description', "like", "%{$slip->employee->last_name} {$slip->employee->first_name}%")
            ->where('description', "like", "%{$slip->year}-%")
            ->get();

        foreach ($entries as $entry) {
            $entry->delete();
        }
    }
}
