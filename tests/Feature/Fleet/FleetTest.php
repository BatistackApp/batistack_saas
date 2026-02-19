<?php

use App\Enums\Fleet\FuelType;
use App\Enums\Fleet\VehicleType;
use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleAssignment;
use App\Models\Fleet\VehicleConsumption;
use App\Models\Projects\Project;
use App\Models\User;
use App\Notifications\Fleet\MaintenanceAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Configuration des permissions de base
    Role::findOrCreate('foreman', 'web');
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'fleet.manage', 'guard_name' => 'web']);

    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo(['fleet.manage']);

    Notification::fake();
    Queue::fake();
});

/**
 * 1. TEST D'ISOLATION SAAS
 */
it('isole les véhicules entre les différents tenants', function () {
    Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'internal_code' => 'V-OURS',
    ]);

    $otherTenant = Tenants::factory()->create();
    Vehicle::factory()->create([
        'tenants_id' => $otherTenant->id,
        'internal_code' => 'V-OTHER',
    ]);

    $response = $this->actingAs($this->user)->getJson('/api/fleet/vehicles');

    $response->assertStatus(200);
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.internal_code'))->toBe('V-OURS');
});

it('peut créer un nouveau véhicule avec tarification analytique', function () {
    $response = $this->actingAs($this->user)->postJson('/api/fleet/vehicles', [
        'name' => 'Camionnette Service',
        'internal_code' => 'CAM-202',
        'type' => VehicleType::Loader->value,
        'fuel_type' => FuelType::Diesel->value,
        'license_plate' => 'AB-123-CD',
        'hourly_rate' => 12.50,
        'km_rate' => 0.45,
        'current_odometer' => 5000,
        'odometer_unit' => 'km',
    ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('vehicles', ['internal_code' => 'CAM-202']);
});

it('calcule correctement le TCO et le coût au kilomètre', function () {
    // 1. Création du véhicule (Base 10 000 km)
    $vehicle = Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'purchase_price' => 20000,
        'current_odometer' => 10000,
    ]);

    // 2. Relevé initial (Début de mois - 10 000 km)
    VehicleConsumption::create([
        'vehicle_id' => $vehicle->id,
        'date' => now()->subMonth()->startOfMonth(),
        'quantity' => 0,
        'amount_ht' => 0,
        'odometer_reading' => 10000,
    ]);

    // 3. Relevé de fin (Fin de mois - 11 000 km)
    // Coût carburant : 200€
    VehicleConsumption::create([
        'vehicle_id' => $vehicle->id,
        'date' => now()->subMonth()->endOfMonth(),
        'quantity' => 100,
        'amount_ht' => 200,
        'odometer_reading' => 11000,
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('fleet.vehicles.tco', [
            'vehicle' => $vehicle->id,
            'start_date' => now()->subMonth()->startOfMonth()->toDateString(),
            'end_date' => now()->toDateString(),
        ]));

    // dd($response->json());

    $response->assertStatus(200)
        ->assertJsonPath('analytics.energy_ht', 200);

    expect($response->json('analytics.cost_per_km'))->toBeGreaterThan(0);
});

it('impute les frais au chantier lors de la libération d\'un véhicule', function () {
    $project = Project::factory()->create(['tenants_id' => $this->tenant->id]);
    $vehicle = Vehicle::factory()->create(['tenants_id' => $this->tenant->id]);

    // 1. Début de l'affectation
    $assignment = VehicleAssignment::create([
        'tenants_id' => $this->tenant->id,
        'vehicle_id' => $vehicle->id,
        'project_id' => $project->id,
        'started_at' => now()->subDays(10),
        'notes' => 'Déplacement Chantier A',
    ]);

    // 2. Frais durant l'affectation (Plein : 150€)
    VehicleConsumption::create([
        'vehicle_id' => $vehicle->id,
        'date' => now()->subDays(5),
        'quantity' => 80,
        'amount_ht' => 150,
        'odometer_reading' => 5000,
    ]);

    // 3. Libération du véhicule
    $response = $this->actingAs($this->user)
        ->patchJson(route('fleet.assignments.release', $assignment));

    $response->assertStatus(200);

    // 4. Vérification de l'imputation analytique
    $this->assertDatabaseHas('project_imputations', [
        'project_id' => $project->id,
        'type' => 'fleet',
    ]);

    $imputation = DB::table('project_imputations')->where('project_id', $project->id)->first();
    // Montant doit être >= 150 (Carburant) + l'amortissement calculé
    expect((float) $imputation->amount)->toBeGreaterThanOrEqual(150.0);
});

/**
 * 6. TEST DE COHÉRENCE ODOMÉTRIQUE
 */
it('empêche de saisir un kilométrage inférieur au kilométrage actuel', function () {
    $vehicle = Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'current_odometer' => 10000,
    ]);

    $response = $this->actingAs($this->user)->postJson(route('fleet.consumptions.store'), [
        'vehicle_id' => $vehicle->id,
        'date' => now()->toDateString(),
        'quantity' => 50,
        'amount_ht' => 100,
        'odometer_reading' => 9000, // Erreur : inférieur à 10000
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['odometer_reading']);
});

/**
 * 2. TEST D'AFFECTATION AUTOMATIQUE
 */
it('clôture automatiquement l\'affectation précédente lors d\'un nouveau mouvement', function () {
    $vehicle = Vehicle::factory()->create(['tenants_id' => $this->tenant->id]);
    $projectA = Project::factory()->create(['tenants_id' => $this->tenant->id]);
    $projectB = Project::factory()->create(['tenants_id' => $this->tenant->id]);

    // Première affectation
    VehicleAssignment::create([
        'vehicle_id' => $vehicle->id,
        'project_id' => $projectA->id,
        'started_at' => now()->subDays(5),
        'tenants_id' => $this->tenant->id,
    ]);

    // Action : Nouvelle affectation au Projet B
    $response = $this->actingAs($this->user)
        ->postJson(route('fleet.assignments.store'), [
            'vehicle_id' => $vehicle->id,
            'project_id' => $projectB->id,
            'started_at' => now(),
            'tenants_id' => $this->tenant->id,
        ]);

    $response->assertStatus(201);

    // Vérification : L'affectation du Projet A doit être terminée
    $oldAssignment = VehicleAssignment::where('project_id', $projectA->id)->first();
    expect($oldAssignment->ended_at)->not->toBeNull();

    // L'affectation du Projet B doit être active
    $newAssignment = VehicleAssignment::where('project_id', $projectB->id)->whereNull('ended_at')->first();
    expect($newAssignment)->not->toBeNull();
});


/**
 * 4. TEST DE VALIDATION DU CODE INTERNE
 */
it('interdit d\'avoir deux véhicules avec le même code interne dans la même entreprise', function () {
    Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'internal_code' => 'CAMION-01',
    ]);

    $response = $this->actingAs($this->user)
        ->postJson('/api/fleet/vehicles', [
            'name' => 'Nouveau Camion',
            'internal_code' => 'CAMION-01', // Doublon
            'type' => VehicleType::Truck->value,
            'fuel_type' => 'diesel',
            'current_odometer' => 0,
            'odometer_unit' => 'km',
            'hourly_rate' => 0,
            'km_rate' => 0,
            'tenants_id' => $this->tenant->id,
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['internal_code']);
});
