<?php

use App\Enums\Locations\RentalStatus;
use App\Models\Locations\RentalContract;
use App\Models\Locations\RentalItem;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Services\Locations\RentalCalculationService;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'locations.manage', 'guard_name' => 'web']);
    $this->tenant = \App\Models\Core\Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo(['locations.manage']);
    $this->tenantsId = $this->tenant->id;

    // Création d'un loueur conforme
    $this->provider = Tiers::factory()->create([
        'tenants_id' => $this->tenantsId,
        'status' => \App\Enums\Tiers\TierStatus::Active,
    ]);

    $this->project = Project::factory()->create(['tenants_id' => $this->tenantsId]);
    $this->phase = ProjectPhase::factory()->create(['project_id' => $this->project->id]);

    $this->calcService = new RentalCalculationService;
});

/*
|--------------------------------------------------------------------------
| TESTS DE WORKFLOW (DRAFT -> ACTIVE -> OFF_HIRE -> ENDED)
|--------------------------------------------------------------------------
*/

it("suit le workflow complet d'une location BTP", function () {
    // 1. Création en Brouillon
    $contract = RentalContract::factory()->create([
        'tenants_id' => $this->tenantsId,
        'status' => RentalStatus::DRAFT,
        'provider_id' => $this->provider->id,
    ]);

    // 2. Activation (Livraison sur chantier)
    $response = $this->actingAs($this->user)
        ->patchJson(route('locations.contracts.update-status', $contract), [
            'status' => RentalStatus::ACTIVE->value,
            'actual_date' => now()->toDateTimeString(),
        ]);

    expect($contract->fresh()->status)->toBe(RentalStatus::ACTIVE)
        ->and($contract->fresh()->actual_pickup_at)->not->toBeNull();

    // 3. Demande d'arrêt (Off-Hire - Fin de facturation)
    $offHireDate = now()->addDays(5);
    $this->actingAs($this->user)
        ->patchJson(route('locations.contracts.update-status', $contract), [
            'status' => RentalStatus::OFF_HIRE->value,
            'actual_date' => $offHireDate->toDateTimeString(),
            'off_hire_reference' => '123456',
        ])
        ->assertStatus(200);

    expect($contract->fresh()->status)->toBe(RentalStatus::OFF_HIRE)
        ->and($contract->fresh()->off_hire_requested_at->toDateString())->toBe($offHireDate->toDateString());

    // 4. Clôture (Retour physique du matériel)
    $this->actingAs($this->user)
        ->patchJson(route('locations.contracts.update-status', $contract), [
            'status' => RentalStatus::ENDED->value,
            'actual_date' => $offHireDate->addDay()->toDateTimeString(),
        ])
        ->assertStatus(200);

    expect($contract->fresh()->status)->toBe(RentalStatus::ENDED);
});

/*
|--------------------------------------------------------------------------
| TESTS DE CALCUL FINANCIER ET DÉGRESSIVITÉ
|--------------------------------------------------------------------------
*/

describe('Moteur de calcul financier', function () {

    it('applique le tarif journalier pour une courte durée', function () {
        $contract = RentalContract::factory()->create(['tenants_id' => $this->tenantsId, 'status' => RentalStatus::ACTIVE]);
        $item = new RentalItem([
            'quantity' => 1,
            'daily_rate_ht' => 100,
            'weekly_rate_ht' => 400, // 80€/j
            'monthly_rate_ht' => 1200, // 40€/j
            'insurance_pct' => 10,
            'is_weekend_included' => false,
            'rental_contract_id' => $contract->id,
        ]);

        $start = CarbonImmutable::parse('2024-02-05'); // Lundi
        $end = CarbonImmutable::parse('2024-02-06');   // Mercredi (2 jours)

        // (100€ * 2j) + 10% assurance = 220€
        $cost = $this->calcService->calculateItemCost($item, $start, $end);
        expect($cost)->toBe(220.0);
    });

    it('bascule sur le tarif semaine si plus avantageux (dégressivité)', function () {
        $contract = RentalContract::factory()->create(['tenants_id' => $this->tenantsId, 'status' => RentalStatus::ACTIVE, 'off_hire_requested_at' => now()->addDay()]);
        $item = new RentalItem([
            'quantity' => 1,
            'daily_rate_ht' => 100,
            'weekly_rate_ht' => 350, // 5 jours de loc
            'monthly_rate_ht' => 1200,
            'insurance_pct' => 0,
            'is_weekend_included' => false,
            'rental_contract_id' => $contract->id,
        ]);

        $start = CarbonImmutable::parse('2024-02-05'); // Lundi
        $end = CarbonImmutable::parse('2024-02-09');   // Samedi (5 jours ouvrés)

        $cost = $this->calcService->calculateItemCost($item, $start, $end);

        // 5 jours à 100€ = 500€, mais le tarif semaine à 350€ doit s'appliquer
        expect($cost)->toBe(350.0);
    });

    it('ne facture pas le weekend par défaut', function () {
        $contract = RentalContract::factory()->create(['tenants_id' => $this->tenantsId, 'status' => RentalStatus::ACTIVE, 'off_hire_requested_at' => now()->addDay()]);
        $item = new RentalItem([
            'quantity' => 1,
            'daily_rate_ht' => 100,
            'insurance_pct' => 0,
            'is_weekend_included' => false,
            'rental_contract_id' => $contract->id,
        ]);

        $start = CarbonImmutable::parse('2024-02-09'); // Vendredi
        $end = CarbonImmutable::parse('2024-02-12');   // Lundi

        // Vendredi + Lundi = 2 jours facturables (Samedi/Dimanche exclus)
        $cost = $this->calcService->calculateItemCost($item, $start, $end);
        expect($cost)->toBe(200.0);
    });

});

/*
|--------------------------------------------------------------------------
| TESTS D'IMPUTATION ET SÉCURITÉ
|--------------------------------------------------------------------------
*/

it('génère une imputation analytique lors de la clôture', function () {
    $contract = RentalContract::factory()->create([
        'tenants_id' => $this->tenantsId,
        'project_id' => $this->project->id,
        'status' => RentalStatus::ACTIVE,
    ]);

    RentalItem::factory()->create([
        'rental_contract_id' => $contract->id,
        'daily_rate_ht' => 100,
        'quantity' => 1,
    ]);

    // On s'assure que la table d'imputation est vide
    DB::table('project_imputations')->truncate();

    $response = $this->actingAs($this->user)
        ->patchJson(route('locations.contracts.update-status', $contract), [
            'status' => RentalStatus::ENDED->value,
            'actual_date' => now()->toDateTimeString(),
        ]);

    $response->assertStatus(200);

    // Vérifie qu'une ligne de coût a été créée pour le projet
    $this->assertDatabaseHas('project_imputations', [
        'project_id' => $this->project->id,
        'type' => 'rental',
    ]);
});
