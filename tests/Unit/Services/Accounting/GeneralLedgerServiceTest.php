<?php

use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;
use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use App\Services\Accounting\GeneralLedgerService;
use Carbon\Carbon;

describe("GeneralLedgerService", function () {
    beforeEach(function () {
        $this->generalLedgerService = app(GeneralLedgerService::class);
    });

    it('génère le grand livre pour un compte', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);
        $account = AccountingAccounts::factory()->create(['tenant_id' => $tenant->id]);

        $entry = AccountingEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'reference' => 'VT20240001',
            'description' => 'Facture 001',
            'status' => 'posted',
            'total_debit' => 1000,
            'total_credit' => 1000,
        ]);

        AccountingEntryLine::factory()->create([
            'accounting_entry_id' => $entry->id,
            'accounting_accounts_id' => $account->id,
            'debit' => 1000,
            'credit' => 0,
        ]);

        $ledger = $this->generalLedgerService->generateForAccount(
            $tenant,
            $account,
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );

        expect($ledger)->toHaveCount(1)
            ->and($ledger->first()['debit'])->toBe(1000.0)
            ->and($ledger->first()['balance'])->toBe(1000.0);
    });

    it('calcule le solde cumulatif', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);
        $account = AccountingAccounts::factory()->create(['tenant_id' => $tenant->id]);

        // Première écriture : débit 500
        $entry1 = AccountingEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'posted_at' => Carbon::make('2024-01-01'),
            'status' => 'posted',
            'total_debit' => 500,
            'total_credit' => 500,
        ]);

        AccountingEntryLine::factory()->create([
            'accounting_entry_id' => $entry1->id,
            'accounting_accounts_id' => $account->id,
            'debit' => 500,
            'credit' => 0,
        ]);

        // Deuxième écriture : crédit 200
        $entry2 = AccountingEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'posted_at' => Carbon::make('2024-01-02'),
            'status' => 'posted',
            'total_debit' => 200,
            'total_credit' => 200,
        ]);

        AccountingEntryLine::factory()->create([
            'accounting_entry_id' => $entry2->id,
            'accounting_accounts_id' => $account->id,
            'debit' => 0,
            'credit' => 200,
        ]);

        $ledger = $this->generalLedgerService->generateForAccount(
            $tenant,
            $account,
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );

        expect($ledger)->toHaveCount(2)
            ->and($ledger[0]['balance'])->toBe(500.0)
            ->and($ledger[1]['balance'])->toBe(300.0);
    });
});
