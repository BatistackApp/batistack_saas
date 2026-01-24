<?php

use App\Models\Accounting\AccountingJournal;
use App\Models\Accounting\AccountingSequence;
use App\Models\Core\Tenant;
use Illuminate\Support\Carbon;

describe("SequenceService", function () {
    beforeEach(function () {
        $this->sequenceService = app(\App\Services\Accounting\SequenceService::class);
    });

    it('génère une référence avec le format correct', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create([
            'tenant_id' => $tenant->id,
            'code' => 'VT',
        ]);

        $reference = $this->sequenceService->generateReference(
            $tenant,
            $journal,
            Carbon::make('2024-01-15')
        );

        expect($reference)->toMatch('/^VT2024\d{4}$/')
            ->and($reference)->toBe('VT20240001');
    });

    it('incrémente le numéro de séquence', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create([
            'tenant_id' => $tenant->id,
            'code' => 'AC',
        ]);

        $ref1 = $this->sequenceService->generateReference(
            $tenant,
            $journal,
            Carbon::make('2024-01-15')
        );
        $ref2 = $this->sequenceService->generateReference(
            $tenant,
            $journal,
            Carbon::make('2024-01-16')
        );

        expect($ref1)->toBe('AC20240001')
            ->and($ref2)->toBe('AC20240002');
    });

    it('réinitialise la séquence pour une nouvelle année', function () {
        $tenant = Tenant::factory()->create();
        $journal = AccountingJournal::factory()->create([
            'tenant_id' => $tenant->id,
            'code' => 'BQ',
        ]);

        $ref2024 = $this->sequenceService->generateReference(
            $tenant,
            $journal,
            Carbon::make('2024-12-31')
        );

        $ref2025 = $this->sequenceService->generateReference(
            $tenant,
            $journal,
            Carbon::make('2025-01-01')
        );

        expect($ref2024)->toMatch('/^BQ2024/')
            ->and($ref2025)->toMatch('/^BQ2025/');
    });

    it('supprime les séquences lors de la réinitialisation annuelle', function () {
        $tenant = Tenant::factory()->create();

        AccountingSequence::factory()->create([
            'tenant_id' => $tenant->id,
            'year' => 2024,
        ]);

        $this->sequenceService->resetForNewYear($tenant, 2024);

        expect(AccountingSequence::where('tenant_id', $tenant->id)
            ->where('year', 2024)
            ->exists())->toBeFalse();
    });
});
