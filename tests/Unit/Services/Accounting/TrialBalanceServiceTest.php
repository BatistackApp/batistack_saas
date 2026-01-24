<?php

use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;
use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use App\Services\Accounting\TrialBalanceService;
use Carbon\Carbon;

describe("TrialBalanceService", function () {
    beforeEach(function () {
        $this->trialBalanceService = app(TrialBalanceService::class);
    });

    it('génère une balance générale', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);

        $account1 = AccountingAccounts::factory()->create([
            'tenant_id' => $tenant->id,
            'number' => '411001',
            'name' => 'Clients',
        ]);
        $account2 = AccountingAccounts::factory()->create([
            'tenant_id' => $tenant->id,
            'number' => '701001',
            'name' => 'Ventes',
        ]);

        $entry = AccountingEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'status' => 'posted',
            'total_debit' => 1000,
            'total_credit' => 1000,
        ]);

        AccountingEntryLine::factory()->create([
            'accounting_entry_id' => $entry->id,
            'accounting_accounts_id' => $account1->id,
            'debit' => 1000,
            'credit' => 0,]);

        AccountingEntryLine::factory()->create([
            'accounting_entry_id' => $entry->id,
            'accounting_accounts_id' => $account2->id,
            'debit' => 0,
            'credit' => 1000,
        ]);

        $balance = $this->trialBalanceService->generate(
            $tenant,
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );

        expect($balance)->toHaveCount(2)
            ->and($balance->sum('debit'))->toBe(1000.0)
            ->and($balance->sum('credit'))->toBe(1000.0);
    });

    it('exclut les comptes avec solde zéro', function () {
        $tenant = Tenant::factory()->create();

        AccountingAccounts::factory()->create([
            'tenant_id' => $tenant->id,
            'is_active' => true,
        ]);

        $balance = $this->trialBalanceService->generate(
            $tenant,
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );

        expect($balance)->toHaveCount(0);
    });

    it('filtre par plage de dates', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);
        $account = AccountingAccounts::factory()->create(['tenant_id' => $tenant->id]);

        $entryJan = AccountingEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'posted_at' => Carbon::make('2024-01-15'),
            'status' => 'posted',
            'total_debit' => 500,
            'total_credit' => 500,
        ]);

        AccountingEntryLine::factory()->create([
            'accounting_entry_id' => $entryJan->id,
            'accounting_accounts_id' => $account->id,
            'debit' => 500,
            'credit' => 0,
        ]);

        $entryDec = AccountingEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'posted_at' => Carbon::make('2024-12-15'),
            'status' => 'posted',
            'total_debit' => 300,
            'total_credit' => 300,
        ]);

        AccountingEntryLine::factory()->create([
            'accounting_entry_id' => $entryDec->id,
            'accounting_accounts_id' => $account->id,
            'debit' => 300,
            'credit' => 0,
        ]);

        $q1Balance = $this->trialBalanceService->generate(
            $tenant,
            Carbon::make('2024-01-01'),
            Carbon::make('2024-03-31')
        );

        expect($q1Balance->first()['debit'])->toBe(500.0);
    });
});
