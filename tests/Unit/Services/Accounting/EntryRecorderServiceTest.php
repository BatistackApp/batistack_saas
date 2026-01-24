<?php

use App\Enums\Accounting\EntryStatus;
use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use App\Services\Accounting\EntryRecorderService;

describe("EntryRecorderService", function () {
    beforeEach(function () {
        $this->recordingService = app(EntryRecorderService::class);
    });

    it('enregistre une écriture équilibrée', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);

        $account1 = AccountingAccounts::factory()->create([
            'tenant_id' => $tenant->id,
            'number' => '411001',
        ]);
        $account2 = AccountingAccounts::factory()->create([
            'tenant_id' => $tenant->id,
            'number' => '701001',
        ]);

        $lines = collect([
            [
                'account_id' => $account1->id,
                'debit' => 1000,
                'credit' => 0,
                'description' => 'Facture client',
            ],
            [
                'account_id' => $account2->id,
                'debit' => 0,
                'credit' => 1000,
                'description' => 'Vente service',
            ],
        ]);

        $entry = $this->recordingService->record(
            $tenant,
            $journal,
            'Test entry',
            $lines
        );

        expect($entry)->toBeInstanceOf(AccountingEntry::class)
            ->and($entry->status)->toBe(EntryStatus::Draft)
            ->and($entry->total_debit)->toBe('1000.00')
            ->and($entry->total_credit)->toBe('1000.00')
            ->and($entry->isBalanced())->toBeTrue();
    });

    it('rejette une écriture déséquilibrée', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);

        $account1 = AccountingAccounts::factory()->create(['tenant_id' => $tenant->id]);
        $account2 = AccountingAccounts::factory()->create(['tenant_id' => $tenant->id]);

        $lines = collect([
            ['account_id' => $account1->id, 'debit' => 1000, 'credit' => 0],
            ['account_id' => $account2->id, 'debit' => 0, 'credit' => 500],
        ]);

        expect(fn () => $this->recordingService->record(
            $tenant,
            $journal,
            'Test',
            $lines
        ))->toThrow(InvalidArgumentException::class, "n'est pas équilibrée");
    });

    it('rejette les lignes vides', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);

        expect(fn () => $this->recordingService->record(
            $tenant,
            $journal,
            'Test',
            collect()
        ))->toThrow(InvalidArgumentException::class, 'au moins une ligne');
    });

    it('poste une écriture en brouillon', function () {
        $entry = AccountingEntry::factory()->create([
            'status' => EntryStatus::Draft,
        ]);

        $posted = $this->recordingService->post($entry);

        expect($posted->status)->toBe(EntryStatus::Posted);
    });

    it('rejette la validation d\'une écriture non-brouillon', function () {
        $entry = AccountingEntry::factory()->create([
            'status' => EntryStatus::Posted,
        ]);

        expect(fn () => $this->recordingService->post($entry))
            ->toThrow(InvalidArgumentException::class, 'brouillon');
    });

    it('valide que account_id est présent', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);

        $lines = collect([
            ['debit' => 1000, 'credit' => 0],
        ]);

        expect(fn () => $this->recordingService->record(
            $tenant,
            $journal,
            'Test',
            $lines
        ))->toThrow(InvalidArgumentException::class, 'account_id');
    });

    it('valide la présence de débit ou crédit', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create(['tenant_id' => $tenant->id]);
        $account = AccountingAccounts::factory()->create(['tenant_id' => $tenant->id]);

        $lines = collect([
            ['account_id' => $account->id],
        ]);

        expect(fn () => $this->recordingService->record(
            $tenant,
            $journal,
            'Test',
            $lines
        ))->toThrow(InvalidArgumentException::class, 'débit soit un crédit');
    });
});
