<?php

use App\Enums\Fleet\VehicleType;
use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleAssignment;
use App\Models\Projects\Project;
use App\Models\User;
use App\Notifications\Fleet\MaintenanceAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
});

/**
 * 1. TEST D'ISOLATION SAAS
 */
it('isole les véhicules entre les différents tenants', function () {
    // Véhicule du tenant A
    Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'internal_code' => 'V-001'
    ]);

    // Véhicule du tenant B
    $otherTenant = Tenants::factory()->create();
    Vehicle::factory()->create([
        'tenants_id' => $otherTenant->id,
        'internal_code' => 'V-002'
    ]);

    $response = $this->actingAs($this->user)
        ->getJson('/api/fleet/vehicles');

    $response->assertStatus(200);
    // On ne doit voir qu'un seul véhicule (celui du tenant A)
    expect($response->json('data'))->toHaveCount(1)
        ->and($response->json('data.0.internal_code'))->toBe('V-001');
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
        ->postJson('/api/fleet/assignments', [
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
 * 3. TEST D'ALERTE MAINTENANCE
 */
it('déclenche une notification de maintenance quand le seuil kilométrique approché est détecté', function () {
    Notification::fake();

    // Création d'un manager de flotte
    $manager = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $manager->assignRole('fleet_manager');

    $vehicle = Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'current_odometer' => 19000, // Seuil de vidange à 20 000 km
    ]);

    // Action : Mise à jour de l'odomètre franchissant le seuil d'alerte (19 500+ km)
    $this->actingAs($this->user)
        ->putJson("/api/fleet/vehicles/{$vehicle->id}", [
            'name' => $vehicle->name,
            'internal_code' => $vehicle->internal_code,
            'type' => $vehicle->type->value,
            'fuel_type' => $vehicle->fuel_type->value,
            'current_odometer' => 19600, // Déclenche l'alerte
            'odometer_unit' => 'km',
            'hourly_rate' => 0,
            'km_rate' => 0,
            'tenants_id' => $this->tenant->id
        ]);

    // Vérification que la notification a été envoyée au manager
    Notification::assertSentTo(
        $manager,
        MaintenanceAlertNotification::class
    );
});

/**
 * 4. TEST DE VALIDATION DU CODE INTERNE
 */
it('interdit d\'avoir deux véhicules avec le même code interne dans la même entreprise', function () {
    Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'internal_code' => 'CAMION-01'
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
            'tenants_id' => $this->tenant->id
        ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors(['internal_code']);
});
