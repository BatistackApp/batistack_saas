<?php

use App\Enums\Commerce\CommandeStatus;
use App\Models\Chantiers\Chantier;
use App\Models\Commerce\Commande;
use App\Models\Commerce\CommandeLigne;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CommandeService', function () {
    beforeEach(function () {
        $this->tenant = Tenant::factory()->create();
        $this->tiers = Tiers::factory()->for($this->tenant)->create();
        $this->chantier = Chantier::factory()->for($this->tenant)->create();
    });

    it('creates a commande with auto-generated number', function () {
        $service = \app('App\Services\Commerce\CommandeService');

        $commande = $service->create([
            'tenant_id' => $this->tenant->id,
            'tiers_id' => $this->tiers->id,
            'chantier_id' => $this->chantier->id,
            'date_commande' => now(),
            'montant_ht' => 0,
            'montant_tva' => 0,
            'montant_ttc' => 0,
        ]);

        expect($commande)->toBeInstanceOf(Commande::class)
            ->and($commande->number)->toMatch('/^CMD-\d{4}-\d{6}$/')
            ->and($commande->status)->toBe(CommandeStatus::Draft);
    });

    it('adds ligne and recalculates totals', function () {
        $commande = Commande::factory()
            ->for($this->tenant)
            ->for($this->tiers)
            ->for($this->chantier)
            ->create();

        $service = \app('App\Services\Commerce\CommandeService');

        $ligne = $service->addLigne($commande, [
            'article_id' => null,
            'quantite_commande' => 5,
            'quantite_livre' => 0,
            'prix_unitaire' => 50,
            'tva' => 'normal',
        ]);

        expect($ligne->montant_ht)->toBe(250.00);
        $commande->refresh();
        expect($commande->montant_ht)->toBe(250.00);
    });

    it('updates delivery quantity', function () {
        $commande = Commande::factory()
            ->for($this->tenant)
            ->for($this->tiers)
            ->for($this->chantier)
            ->has(CommandeLigne::factory())
            ->create();

        $service = \app('App\Services\Commerce\CommandeService');
        $ligne = $commande->lignes()->first();

        $updated = $service->updateDeliveryQuantity($ligne, 5);

        expect($updated->quantite_livre)->toBe(5.0);
    });

    it('confirms a commande', function () {
        $commande = Commande::factory()
            ->for($this->tenant)
            ->for($this->tiers)
            ->for($this->chantier)
            ->create();

        $service = \app('App\Services\Commerce\CommandeService');
        $confirmed = $service->confirm($commande);

        expect($confirmed->status)->toBe(CommandeStatus::Confirmed);
    });
});
