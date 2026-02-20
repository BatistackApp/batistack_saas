<?php

use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\MaintenanceType;
use App\Enums\Fleet\VehicleType;
use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleMaintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Initialisation des permissions et des données de base
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'fleet.manage', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'fleet.manage', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->assignRole($role);
    $this->user->givePermissionTo(['fleet.manage']);

    $this->actingAs($this->user);

    $this->vehicle = Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'type' => VehicleType::Truck,
        'current_odometer' => 50000,
    ]);

    Notification::fake();
});

/**
 * --- TESTS DES PLANS DE MAINTENANCE ---
 */
it('peut créer un plan de maintenance pour un type de véhicule', function () {
    $payload = [
        'name' => 'Révision 50 000 km',
        'vehicle_type' => VehicleType::Truck->value,
        'interval_km' => 50000,
        'operations' => ['Vidange moteur', 'Remplacement filtre air'],
        'tenants_id' => $this->tenant->id,
    ];

    $response = $this->postJson('/api/fleet/maintenance-plans', $payload);

    $response->assertStatus(201);
    $this->assertDatabaseHas('vehicle_maintenance_plans', [
        'name' => 'Révision 50 000 km',
        'interval_km' => 50000,
    ]);
});

/**
 * --- TESTS DU CYCLE DE VIE DES INTERVENTIONS ---
 */
it('peut signaler une nouvelle panne sur un véhicule', function () {
    $payload = [
        'vehicle_id' => $this->vehicle->id,
        'maintenance_type' => MaintenanceType::Curative->value,
        'description' => 'Fuite hydraulique sur le bras arrière',
        'odometer_reading' => 50100,
        'tenants_id' => $this->tenant->id,
    ];

    $response = $this->postJson('/api/fleet/maintenances', $payload);

    $response->assertStatus(201)
        ->assertJsonPath('data.maintenance_status', MaintenanceStatus::Reported->value);

    $this->assertDatabaseHas('vehicle_maintenances', [
        'vehicle_id' => $this->vehicle->id,
        'description' => 'Fuite hydraulique sur le bras arrière',
        'maintenance_status' => MaintenanceStatus::Reported->value,
    ]);
});

it('met à jour le statut lors du démarrage des travaux', function () {
    $maintenance = VehicleMaintenance::create([
        'tenants_id' => $this->tenant->id,
        'vehicle_id' => $this->vehicle->id,
        'internal_reference' => 'MTN-2026-TEST',
        'maintenance_type' => MaintenanceType::Curative,
        'maintenance_status' => MaintenanceStatus::Reported,
        'reported_at' => now(),
    ]);

    $response = $this->patchJson("/api/fleet/maintenances/{$maintenance->id}/start");

    $response->assertStatus(200);
    expect($maintenance->fresh()->maintenance_status)->toBe(MaintenanceStatus::InProgress)
        ->and($maintenance->fresh()->started_at)->not->toBeNull();
});

it('clôture une maintenance et calcule le temps d\'immobilisation', function () {
    $startedAt = now()->subHours(5);

    $maintenance = VehicleMaintenance::create([
        'tenants_id' => $this->tenant->id,
        'vehicle_id' => $this->vehicle->id,
        'internal_reference' => 'MTN-2026-TEST',
        'maintenance_type' => MaintenanceType::Preventative,
        'maintenance_status' => MaintenanceStatus::InProgress,
        'started_at' => $startedAt,
    ]);

    $payload = [
        'cost_parts' => 150.50,
        'cost_labor' => 200.00,
        'resolution_notes' => 'Vidange effectuée et filtres changés',
        'completed_at' => now(),
        'odometer_reading' => 50200,
        'hours_reading' => 1200,
    ];

    $response = $this->patchJson("/api/fleet/maintenances/{$maintenance->id}/complete", $payload);

    $response->assertStatus(200);

    $updated = $maintenance->fresh();
    expect($updated->maintenance_status)->toBe(MaintenanceStatus::Completed)
        ->and((float) $updated->total_cost)->toBe(350.50)
        ->and($updated->downtime_hours)->toBeGreaterThanOrEqual(5);
});

it('clôture une maintenance et met à jour les coûts', function () {
    $maintenance = VehicleMaintenance::create([
        'vehicle_id' => $this->vehicle->id,
        'internal_reference' => 'MTN-COMPLETE-TEST',
        'maintenance_type' => MaintenanceType::Preventative,
        'maintenance_status' => MaintenanceStatus::InProgress,
        'started_at' => now()->subHours(5),
    ]);

    $payload = [
        'cost_parts' => 150,
        'cost_labor' => 200,
        'resolution_notes' => 'Réparé',
        'completed_at' => now(),
        'odometer_reading' => 50300,
        'hours_reading' => 1250,
    ];

    $response = $this->patchJson("/api/fleet/maintenances/{$maintenance->id}/complete", $payload);

    $response->assertStatus(200);
    $this->assertDatabaseHas('vehicle_maintenances', [
        'id' => $maintenance->id,
        'maintenance_status' => MaintenanceStatus::Completed->value,
        'cost_parts' => 150,
    ]);
});

it('isole les maintenances par tenant (Sécurité)', function () {
    $otherTenant = Tenants::factory()->create();

    // On crée une maintenance pour un autre tenant sans déclencher les events du trait
    $otherMaintenance = VehicleMaintenance::withoutEvents(function () use ($otherTenant) {
        return VehicleMaintenance::factory()->create([
            'tenants_id' => $otherTenant->id,
            'vehicle_id' => Vehicle::factory()->create(['tenants_id' => $otherTenant->id])->id,
            'internal_reference' => 'SECRET-MTN',
        ]);
    });

    $response = $this->getJson("/api/fleet/maintenances/{$otherMaintenance->id}");

    $response->assertStatus(404);
});
