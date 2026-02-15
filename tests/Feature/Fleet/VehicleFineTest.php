<?php

use App\Enums\Fleet\DesignationStatus;
use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleFine;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'fleet.manage', 'guard_name' => 'web']);
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create([
        'tenants_id' => $this->tenant->id
    ]);
    $this->user->givePermissionTo(['fleet.manage']);

    $this->vehicle = Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'license_plate' => 'AB-123-CD'
    ]);

    Notification::fake();
    Queue::fake();
});

it('peut lister uniquement les contraventions de son propre tenant', function () {
    // Fine du tenant actuel
    VehicleFine::factory()->create([
        'tenants_id' => $this->tenant->id,
        'vehicle_id' => $this->vehicle->id
    ]);

    // Fine d'un autre tenant
    $otherTenant = Tenants::factory()->create();
    VehicleFine::factory()->create([
        'tenants_id' => $otherTenant->id
    ]);

    $response = $this->actingAs($this->user)
        ->getJson(route('fleet.fines.index'));

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

it('enregistre une nouvelle contravention avec validation', function () {
    $payload = [
        'vehicle_id' => $this->vehicle->id,
        'notice_number' => '1234567890',
        'offense_at' => now()->subDays(2)->format('Y-m-d H:i:s'),
        'amount_initial' => 135.00,
        'type' => 'Excès de vitesse < 20km/h',
        'tenants_id' => $this->tenant->id
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('fleet.fines.store'), $payload);

    $response->assertStatus(201);

    $this->assertDatabaseHas('vehicle_fines', [
        'notice_number' => '1234567890',
        'tenants_id' => $this->tenant->id,
        'designation_status' => DesignationStatus::Pending->value
    ]);
});

it('refuse l\'export ANTAI si aucun chauffeur n\'est assigné', function () {
    $fine = VehicleFine::factory()->create([
        'tenants_id' => $this->tenant->id,
        'user_id' => null, // Pas de chauffeur
        'designation_status' => DesignationStatus::Pending
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('fines.export-antai'), [
            'ids' => [$fine->id]
        ]);

    $response->assertStatus(422)
        ->assertJsonFragment(['error' => 'Aucune contravention éligible sélectionnée.']);
});

it('génère un fichier d\'export et met à jour le statut des amendes', function () {
    Storage::fake('public');

    $driver = User::factory()->create(['tenants_id' => $this->tenant->id]);

    $fine = VehicleFine::factory()->create([
        'tenants_id' => $this->tenant->id,
        'vehicle_id' => $this->vehicle->id,
        'user_id' => $driver->id,
        'designation_status' => DesignationStatus::Pending
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('fines.export-antai'), [
            'ids' => [$fine->id]
        ]);

    // Vérifie que c'est un téléchargement de fichier (StreamedResponse)
    $response->assertStatus(200)
        ->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

    // Vérifie le changement de statut en base
    expect($fine->fresh()->designation_status)->toBe(DesignationStatus::Exported);
});
