<?php

use App\Enums\Locations\RentalStatus;
use App\Models\Locations\RentalContract;
use App\Models\Locations\RentalItem;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use App\Models\User;
use App\Services\Locations\RentalCalculationService;
use App\Services\Locations\RentalCostImputationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

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

test('le calcul des jours facturables exclut les weekends par défaut', function () {
    $item = new RentalItem([
        'is_weekend_included' => false,
        'quantity' => 1,
    ]);

    // Du vendredi au lundi suivant = 4 jours calendaires, mais seulement 2 jours ouvrés (ven, lun)
    // Note : La logique de diffInDaysFiltered inclut les bornes si configuré ainsi
    $start = Carbon::parse('2026-02-06'); // Vendredi
    $end = Carbon::parse('2026-02-09');   // Lundi

    // Réflexion pour accéder à la méthode privée de test
    $method = new \ReflectionMethod(RentalCalculationService::class, 'getBillableDays');
    $days = $method->invoke($this->calcService, $item, $start, $end);

    expect($days)->toBe(2);
});

test('le moteur de calcul choisit le tarif hebdomadaire pour une durée de 6 jours', function () {
    $item = new RentalItem([
        'quantity' => 1,
        'daily_rate_ht' => 100,
        'weekly_rate_ht' => 400, // Moins cher que 6 * 100
        'monthly_rate_ht' => 1500,
        'insurance_pct' => 0,
        'is_weekend_included' => true,
    ]);

    $start = now();
    $end = now()->addDays(5); // 6 jours inclusifs

    $cost = $this->calcService->calculateItemCost($item, $start, $end);

    // 6 jours >= 5 jours -> Utilise le tarif semaine au prorata (6/5 * 400) = 480
    expect((float) $cost)->toBe(480.0);
});

test('on ne peut pas activer une location si le loueur est non conforme', function () {
    // On rend le loueur non conforme (ex: documents périmés)
    // Note : On simule ici la réponse du service de conformité
    $this->provider->update(['status' => \App\Enums\Tiers\TierStatus::Suspended]);

    $contract = RentalContract::factory()->create([
        'tenants_id' => $this->tenantsId,
        'provider_id' => $this->provider->id,
        'status' => RentalStatus::DRAFT,
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson(route('locations.contracts.update-status', $contract), [
            'status' => RentalStatus::ACTIVE->value,
            'actual_date' => now()->toDateTimeString(),
        ]);

    $response->assertStatus(422);
    expect($response->json('error'))->toContain("Le loueur n'est pas à jour");
});

test('le job d\'imputation journalière crée des lignes de coûts sur le projet', function () {
    $contract = RentalContract::factory()->create([
        'tenants_id' => $this->tenantsId,
        'project_id' => $this->project->id,
        'project_phase_id' => $this->phase->id,
        'status' => RentalStatus::ACTIVE,
    ]);

    $contract->items()->create([
        'label' => 'Pelle 15T',
        'quantity' => 1,
        'daily_rate_ht' => 250,
        'weekly_rate_ht' => 1000,
        'monthly_rate_ht' => 3000,
        'is_weekend_included' => true,
    ]);

    $service = app(RentalCostImputationService::class);
    $service->imputeDailyCost($contract);

    $this->assertDatabaseHas('project_imputations', [
        'project_id' => $this->project->id,
        'type' => 'rental',
        'amount' => 250.00,
    ]);
});

test('la clôture d\'un contrat calcule le coût final et fige les dates', function () {
    $contract = RentalContract::factory()->create([
        'tenants_id' => $this->tenantsId,
        'status' => RentalStatus::ACTIVE,
        'actual_pickup_at' => now()->subDays(2),
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson(route('locations.contracts.update-status', $contract), [
            'status' => RentalStatus::ENDED->value,
            'actual_date' => now()->toDateTimeString(),
        ]);

    $response->assertStatus(200);
    expect($contract->refresh()->status)->toBe(RentalStatus::ENDED)
        ->and($contract->actual_return_at)->not->toBeNull();
});
