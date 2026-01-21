<?php

use App\Enums\Commerce\DocumentStatus;
use App\Models\Chantiers\Chantier;
use App\Models\Commerce\Devis;
use App\Models\Commerce\DevisLigne;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('DevisService', function () {

    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();
        $this->tiers = Tiers::factory()->for($this->tenant)->create();
        $this->chantier = Chantier::factory()->for($this->tenant)->create();
    });

    it('creates a devis with auto-generated number', function () {
        $response = \app('App\Services\Commerce\DevisService')->create([
            'tenant_id' => $this->tenant->id,
            'tiers_id' => $this->tiers->id,
            'chantier_id' => $this->chantier->id,
            'date_emission' => now(),
            'date_validite' => now()->addDays(30),
            'montant_ht' => 0,
            'montant_tva' => 0,
            'montant_ttc' => 0,
        ]);

        expect($response)->toBeInstanceOf(Devis::class)
            ->and($response->number)->toMatch('/^DV-\d{4}-\d{6}$/')
            ->and($response->status)->toBe(DocumentStatus::Draft->value);
    });

    it('adds a ligne to devis and recalculates totals', function () {
        $devis = Devis::factory()
            ->for($this->tenant)
            ->for($this->tiers)
            ->for($this->chantier)
            ->create();

        $service = \app('App\Services\Commerce\DevisService');

        $ligne = $service->addLigne($devis, [
            'article_id' => null,
            'description' => 'Test article',
            'quantite' => 2,
            'prix_unitaire' => 100,
            'tva' => 'normal',
        ]);

        expect($ligne)->toBeInstanceOf(DevisLigne::class)
            ->and($ligne->montant_ht)->toBe(200.00);

        $devis->refresh();
        expect($devis->montant_ht)->toBe(200.00)
            ->and($devis->montant_ttc)->toBeGreaterThan(200.00);
    });

    it('removes a ligne and recalculates totals', function () {
        $devis = Devis::factory()
            ->for($this->tenant)
            ->for($this->tiers)
            ->for($this->chantier)
            ->has(DevisLigne::factory()->count(2))
            ->create();

        $service = \app('App\Services\Commerce\DevisService');
        $firstLigne = $devis->lignes()->first();

        $service->removeLigne($firstLigne);

        expect($devis->lignes)->toHaveCount(1);
    });

    it('validates a devis', function () {
        $devis = Devis::factory()
            ->for($this->tenant)
            ->for($this->tiers)
            ->for($this->chantier)
            ->create();

        $service = \app('App\Services\Commerce\DevisService');
        $validated = $service->validate($devis);

        expect($validated->status)->toBe(DocumentStatus::Validated);
    });

    it('cancels a devis', function () {
        $devis = Devis::factory()
            ->for($this->tenant)
            ->for($this->tiers)
            ->for($this->chantier)
            ->create();

        $service = \app('App\Services\Commerce\DevisService');
        $cancelled = $service->cancel($devis);

        expect($cancelled->status)->toBe(DocumentStatus::Cancelled);
    });
});
