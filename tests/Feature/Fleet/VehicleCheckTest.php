<?php

use App\Enums\Fleet\VehicleType;
use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleAssignment;
use App\Models\Fleet\VehicleCheck;
use App\Models\Fleet\VehicleChecklistQuestion;
use App\Models\Fleet\VehicleChecklistTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Initialisation des permissions et des données de base
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'fleet.manage', 'guard_name' => 'web']);
    $role = Role::firstOrCreate(['name' => 'tenant_amin', 'guard_name' => 'web']);

    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->assignRole($role);
    $this->user->givePermissionTo(['fleet.manage']);

    // Création d'un véhicule de test
    $this->vehicle = Vehicle::factory()->create([
        'tenants_id' => $this->tenant->id,
        'type' => VehicleType::Truck,
        'current_odometer' => 10000,
    ]);

    $this->actingAs($this->user);

    Notification::fake();
});

it('peut créer un template de checklist avec ses questions en une seule fois', function () {
    $payload = [
        'name' => 'Checklist Quotidienne PL',
        'vehicle_type' => VehicleType::Truck->value,
        'is_active' => true,
        'questions' => [
            [
                'label' => 'Vérification des niveaux',
                'response_type' => 'boolean',
                'is_mandatory' => true,
                'sort_order' => 1,
            ],
            [
                'label' => 'État de la carrosserie',
                'response_type' => 'boolean',
                'is_mandatory' => true,
                'sort_order' => 2,
            ],
        ],
    ];

    $response = $this->postJson(route('fleet.checklist-templates.store'), $payload);

    $response->assertStatus(201);

    $this->assertDatabaseHas('vehicle_checklist_templates', [
        'name' => 'Checklist Quotidienne PL',
        'tenants_id' => $this->tenant->id,
    ]);

    $this->assertDatabaseCount('vehicle_checklist_questions', 2);
});

/**
 * --- TESTS DES CONTRÔLES (OPÉRATIONNEL / MOBILE) ---
 */
it('récupère le bon template en fonction du type de véhicule', function () {
    // On crée un template spécifique pour les Camions
    $template = VehicleChecklistTemplate::factory()->create([
        'tenants_id' => $this->tenant->id,
        'vehicle_type' => VehicleType::Truck,
        'is_active' => true,
    ]);

    // On ajoute une question
    VehicleChecklistQuestion::factory()->create(['template_id' => $template->id, 'label' => 'Pression pneus']);

    $response = $this->getJson(route('fleet.checks.get-template', $this->vehicle));

    $response->assertStatus(200)
        ->assertJsonPath('id', $template->id)
        ->assertJsonFragment(['label' => 'Pression pneus']);
});

it('enregistre un contrôle de prise de poste et met à jour les kilomètres du véhicule', function () {
    $template = VehicleChecklistTemplate::factory()->create([
        'tenants_id' => $this->tenant->id,
        'vehicle_type' => VehicleType::Truck,
    ]);

    $question = VehicleChecklistQuestion::factory()->create(['template_id' => $template->id]);

    $assignment = VehicleAssignment::factory()->create([
        'tenants_id' => $this->tenant->id,
        'vehicle_id' => $this->vehicle->id,
        'user_id' => $this->user->id,
    ]);

    $payload = [
        'vehicle_id' => $this->vehicle->id,
        'vehicle_assignment_id' => $assignment->id,
        'type' => 'start',
        'odometer_reading' => 10250, // Le véhicule était à 10000
        'results' => [
            [
                'question_id' => $question->id,
                'value' => 'ok',
                'anomaly_description' => null,
            ],
        ],
    ];

    $response = $this->postJson(route('fleet.checks.store'), $payload);

    $response->assertStatus(201);

    // 1. Vérifie que le rapport existe
    $this->assertDatabaseHas('vehicle_checks', [
        'vehicle_id' => $this->vehicle->id,
        'odometer_reading' => 10250,
        'has_anomalie' => false,
    ]);

    // 2. Vérifie la mise à jour automatique des km du véhicule
    expect($this->vehicle->fresh()->current_odometer)->toBe('10250.00');
});

it('détecte automatiquement une anomalie si une réponse est "ko"', function () {
    $template = VehicleChecklistTemplate::factory()->create(['tenants_id' => $this->tenant->id, 'vehicle_type' => VehicleType::Truck]);
    $question = VehicleChecklistQuestion::factory()->create(['template_id' => $template->id]);

    $payload = [
        'vehicle_id' => $this->vehicle->id,
        'type' => 'end',
        'odometer_reading' => 10300,
        'results' => [
            [
                'question_id' => $question->id,
                'value' => 'ko',
                'anomaly_description' => 'Feu avant droit cassé',
            ],
        ],
    ];

    $response = $this->postJson(route('fleet.checks.store'), $payload);

    $response->assertStatus(201);

    // Le flag has_anomalie doit être à true
    $this->assertDatabaseHas('vehicle_checks', [
        'id' => $response->json('check.id'),
        'has_anomalie' => true,
    ]);
});

/**
 * --- TESTS DE SÉCURITÉ SAAS ---
 */
it('interdit de voir les rapports de contrôle d\'un autre tenant', function () {
    $otherTenant = Tenants::factory()->create();

    // Check du tenant actuel
    VehicleCheck::factory()->create(['tenants_id' => $this->tenant->id, 'user_id' => $this->user->id, 'vehicle_id' => $this->vehicle->id]);

    // Check d'un autre tenant
    $otherUser = User::factory()->create(['tenants_id' => $otherTenant->id]);
    $otherVehicle = Vehicle::factory()->create(['tenants_id' => $otherTenant->id]);
    VehicleCheck::factory()->create(['tenants_id' => $otherTenant->id, 'user_id' => $otherUser->id, 'vehicle_id' => $otherVehicle->id]);

    $response = $this->getJson(route('fleet.checks.index'));

    $response->assertStatus(200);
    // On ne doit voir que son propre check
    $response->assertJsonCount(1, 'data');
});
