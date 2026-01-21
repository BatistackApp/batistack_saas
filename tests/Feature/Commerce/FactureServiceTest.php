<?php

use App\Enums\Commerce\DocumentStatus;
use App\Models\Chantiers\Chantier;
use App\Models\Commerce\Facture;
use App\Models\Commerce\FactureLigne;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('FactureService', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();
        $this->tiers = Tiers::factory()->for($this->tenant)->create();
        $this->chantier = Chantier::factory()->for($this->tenant)->create();
    });

    it('creates a facture with auto-generated number', function () {
        $service = \app('App\Services\Commerce\FactureService');

        $facture = $service->create([
            'tenant_id' => $this->tenant->id,
            'tiers_id' => $this->tiers->id,
            'chantier_id' => $this->chantier->id,
            'date_facture' => now(),
            'date_echeance' => now()->addDays(30),
            'montant_ht' => 0,
            'montant_tva' => 0,
            'montant_ttc' => 0,
            'montant_paye' => 0,
        ]);

        expect($facture)->toBeInstanceOf(Facture::class);
        expect($facture->number)->toMatch('/^FAC-\d{4}-\d{6}$/');
    });

    it('tracks payment status correctly', function () {
        $facture = Facture::factory()
            ->for($this->tenant)
            ->for($this->tiers)
            ->for($this->chantier)
            ->has(FactureLigne::factory())
            ->create(['montant_ttc' => 1000]);

        $service = \app('App\Services\Commerce\FactureService');

        expect($facture->status)->toBe(DocumentStatus::Invoiced);

        $service->addPayment($facture, [
            'date_paiement' => now(),
            'montant' => 500,
            'type_paiement' => 'virement',
        ]);

        $facture->refresh();
        expect($facture->status)->toBe(DocumentStatus::PartiallyPaid);
        expect($facture->montant_paye)->toBe(500.00);

        $service->addPayment($facture, [
            'date_paiement' => now(),
            'montant' => 500,
            'type_paiement' => 'cheque',
        ]);

        $facture->refresh();
        expect($facture->status)->toBe(DocumentStatus::Paid);
    });
});
