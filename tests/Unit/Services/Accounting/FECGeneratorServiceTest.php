<?php

use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingEntryLine;
use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use App\Services\Accounting\FECGeneratorService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("FEC Generator Service", function () {
    beforeEach(function () {
        $this->fecService = app(FECGeneratorService::class);
    });

    it('génère un fichier FEC au format CSV', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create([
            'tenant_id' => $tenant->id,
            'code' => 'VT',
            'name' => 'Ventes',
        ]);

        $account = AccountingAccounts::factory()->create([
            'tenant_id' => $tenant->id,
            'number' => '411001',
            'name' => 'Clients',
        ]);

        $entry = AccountingEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'reference' => 'VT20240001',
            'description' => 'Facture client',
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

        $fec = $this->fecService->generate(
            $tenant,
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );

        expect($fec)->toContain('JournalCode')
            ->and($fec)->toContain('VT')
            ->and($fec)->toContain('VT20240001')
            ->and($fec)->toContain('411001');
    });

    it('valide le format FEC', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);
        $account = AccountingAccounts::factory()->create(['tenant_id' => $tenant->id]);

        $entry = AccountingEntry::factory()->create([
            'tenant_id' => $tenant->id,
            'accounting_journal_id' => $journal->id,
            'status' => 'posted',
        ]);

        AccountingEntryLine::factory()->create([
            'accounting_entry_id' => $entry->id,
            'accounting_accounts_id' => $account->id,
            'debit' => 100,
            'credit' => 0,
        ]);

        $fec = $this->fecService->generate(
            $tenant,
            Carbon::now()->startOfYear(),
            Carbon::now()->endOfYear()
        );

        $lines = explode("\n", trim($fec));
        expect(count($lines))->toBeGreaterThan(1);

        $headers = str_getcsv($lines[0]);
        expect(in_array('JournalCode', $headers))->toBeTrue()
            ->and(in_array('EcritureDebit', $headers))->toBeTrue();
    });
});
