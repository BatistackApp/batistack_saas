<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use App\Models\User;
use App\Services\Accounting\EntryRecorderService;

uses(RefreshDatabase::class);

describe("Accounting Entry", function () {
    beforeEach(function () {
        $this->recordingService = app(EntryRecorderService::class);
        $this->tenant = Tenant::factory()->create();
        $this->journal = AccountingJournal::factory()->create([
            'tenant_id' => $this->tenant->id,
        ]);
        $this->user = User::factory()->create();
    });

    it('crée une écriture comptable', function () {
        $account1 = AccountingAccounts::factory()->create(['tenant_id' => $this->tenant->id]);
        $account2 = AccountingAccounts::factory()->create(['tenant_id' => $this->tenant->id]);

        $lines = collect([
            [
                'account_id' => $account1->id,
                'debit' => 1000,
                'credit' => 0,
            ],
            [
                'account_id' => $account2->id,
                'debit' => 0,
                'credit' => 1000,
            ],
        ]);

        $entry = $this->recordingService->record(
            $this->tenant,
            $this->journal,
            'Test entry',
            $lines
        );

        expect($entry->exists)->toBeTrue()
            ->and($entry->lines)->toHaveCount(2);
    });

    it('maintient l\'équilibre de l\'écriture', function () {
        $account1 = AccountingAccounts::factory()->create(['tenant_id' => $this->tenant->id]);
        $account2 = AccountingAccounts::factory()->create(['tenant_id' => $this->tenant->id]);

        $lines = collect([
            [
                'account_id' => $account1->id,
                'debit' => 2500.50,
                'credit' => 0,
            ],
            [
                'account_id' => $account2->id,
                'debit' => 0,
                'credit' => 2500.50,
            ],
        ]);

        $entry = $this->recordingService->record(
            $this->tenant,
            $this->journal,
            'Test entry',
            $lines
        );

        expect($entry->exists)
            ->toBeTrue()
            ->and($entry->lines)->toHaveCount(2);
    });

    it('poste une écriture comptable', function () {
        $account1 = AccountingAccounts::factory()->create(['tenant_id' => $this->tenant->id]);
        $account2 = AccountingAccounts::factory()->create(['tenant_id' => $this->tenant->id]);

        $lines = collect([
            [
                'account_id' => $account1->id,
                'debit' => 1000,
                'credit' => 0,
            ],
            [
                'account_id' => $account2->id,
                'debit' => 0,
                'credit' => 1000,
            ],
        ]);

        $entry = $this->recordingService->record(
            $this->tenant,
            $this->journal,
            'Test entry',
            $lines
        );

        $posted = $this->recordingService->post($entry);

        expect($posted->status->value)->toBe('posted');
    });
});


