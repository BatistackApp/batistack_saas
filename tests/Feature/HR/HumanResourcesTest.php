<?php

use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();

    // Configuration des rôles nécessaires pour les tests HR
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    Role::findOrCreate('tenant_admin', 'web');

    // Création d'un administrateur de tenant
    $this->admin = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->admin->assignRole('tenant_admin');

    $this->actingAs($this->admin);
});

test('un administrateur peut lister les employés du tenant', function () {
    Employee::factory()->count(3)->create(['tenants_id' => $this->tenant->id]);
    // Employé d'un autre tenant (ne doit pas apparaître)
    Employee::factory()->create(['tenants_id' => Tenants::factory()->create()->id]);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/hr/employees');

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('un administrateur peut voir les détails d’un employé spécifique', function () {
    $employee = Employee::factory()->create(['tenants_id' => $this->tenant->id]);

    $response = $this->actingAs($this->admin)
        ->getJson("/api/hr/employees/{$employee->id}");

    $response->assertStatus(200)
        ->assertJsonPath('first_name', $employee->first_name);
});

test('peut créer un collaborateur avec tous les champs obligatoires', function () {
    Notification::fake();
    $data = [
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'job_title' => 'Chef de Chantier',
        'department' => 'Gros Œuvre',
        'hourly_cost_charged' => 55.00,
        'contract_type' => 'CDI',
        'hired_at' => now()->format('Y-m-d'),
        'is_active' => true,
        'email' => 'test@test.com',
    ];

    $response = $this->actingAs($this->admin)
        ->postJson('/api/hr/employees', $data);

    $response->assertStatus(201);

    $this->assertDatabaseHas('employees', [
        'tenants_id' => $this->tenant->id,
        'last_name' => 'Dupont',
        'hourly_cost_charged' => 55.00,
    ]);
});

test('la création d’un employé échoue si les données sont incomplètes', function () {
    $response = $this->actingAs($this->admin)
        ->postJson('/api/hr/employees', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name', 'last_name', 'hourly_cost_charged']);
});

test('peut mettre à jour les informations d’un collaborateur', function () {
    $employee = Employee::factory()->create([
        'tenants_id' => $this->tenant->id,
        'job_title' => 'Ancien Poste',
    ]);

    $response = $this->actingAs($this->admin)
        ->patchJson("/api/hr/employees/{$employee->id}", [
            'job_title' => 'Nouveau Poste',
            'is_active' => false,
        ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('employees', [
        'id' => $employee->id,
        'job_title' => 'Nouveau Poste',
        'is_active' => false,
    ]);
});

test('peut supprimer un collaborateur (soft delete)', function () {
    $employee = Employee::factory()->create(['tenants_id' => $this->tenant->id]);

    $response = $this->actingAs($this->admin)
        ->deleteJson("/api/hr/employees/{$employee->id}", []);

    $response->assertStatus(200);

    // Vérification Soft Delete
    $this->assertSoftDeleted('employees', ['id' => $employee->id]);
});

/**
 * Tests de recherche et filtres
 */
test('peut filtrer les employés par département', function () {
    Employee::factory()->create(['tenants_id' => $this->tenant->id, 'department' => 'IT']);
    Employee::factory()->create(['tenants_id' => $this->tenant->id, 'department' => 'RH']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/hr/employees?department=IT');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.department', 'IT');
});

test('peut rechercher un employé par son nom ou prénom', function () {
    Employee::factory()->create(['tenants_id' => $this->tenant->id, 'first_name' => 'Albert']);
    Employee::factory()->create(['tenants_id' => $this->tenant->id, 'first_name' => 'Zoe']);

    $response = $this->actingAs($this->admin)
        ->getJson('/api/hr/employees?search=Alb');

    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.first_name', 'Albert');
});
