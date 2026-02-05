<?php

use App\Enums\HR\TimeEntryStatus;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\HR\TimeEntry;
use App\Models\Projects\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create();
    // Simulation du header X-Tenant-Id utilisé dans le contrôleur
    $this->headers = ['X-Tenant-Id' => $this->tenant->id];
});

test('peut lister les employés du tenant', function () {
    Employee::factory()->count(3)->create(['tenants_id' => $this->tenant->id]);

    $response = $this->getJson('/api/hr/employees', $this->headers);

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('peut créer un employé avec les champs spécifiques', function () {
    $data = [
        'tenants_id' => $this->tenant->id,
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'job_title' => 'Développeur',
        'hourly_cost_charged' => 45.50,
        'contract_type' => 'CDI',
        'hired_at' => now()->format('Y-m-d'),
        'is_active' => true,
        'user_id' => $this->user->id,
    ];

    $response = $this->postJson('/api/hr/employees', $data, $this->headers);

    $response->assertStatus(201);
    $this->assertDatabaseHas('employees', [
        'first_name' => 'Jean',
        'hourly_cost_charged' => 45.50,
    ]);
});

test('ne peut pas créer un employé sans le coût horaire', function () {
    $response = $this->postJson('/api/hr/employees', [
        'first_name' => 'Jean',
    ], $this->headers);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['hourly_cost_charged']);
});

/*
|--------------------------------------------------------------------------
| Tests Pointage (Time Entries)
|--------------------------------------------------------------------------
*/

test('peut enregistrer un pointage avec temps de trajet et indemnités', function () {
    $employee = Employee::factory()->create(['tenants_id' => $this->tenant->id]);
    $project = Project::factory()->create(['tenants_id' => $this->tenant->id]);

    $data = [
        'tenants_id' => $this->tenant->id,
        'employee_id' => $employee->id,
        'project_id' => $project->id,
        'date' => now()->format('Y-m-d'),
        'hours' => 7.5,
        'travel_time' => 1.25,
        'has_meal_allowance' => true,
        'has_host_allowance' => false,
        'status' => TimeEntryStatus::Draft->value,
    ];

    $response = $this->postJson('/api/hr/time-entries', $data, $this->headers);

    $response->assertStatus(201);
    $this->assertDatabaseHas('time_entries', [
        'employee_id' => $employee->id,
        'hours' => 7.5,
        'travel_time' => 1.25,
        'has_meal_allowance' => true,
    ]);
});

test('un responsable peut valider un pointage', function () {
    Event::fake();
    $entry = TimeEntry::factory()->create([
        'tenants_id' => $this->tenant->id,
        'status' => TimeEntryStatus::Draft->value,
    ]);

    $response = $this->patchJson("/api/hr/time-entries/{$entry->id}/verify", [
        'status' => TimeEntryStatus::Approved->value,
        'verified_by' => $this->user->id,
    ], $this->headers);

    $response->assertStatus(200);
    $this->assertDatabaseHas('time_entries', [
        'id' => $entry->id,
        'status' => TimeEntryStatus::Approved->value,
        'verified_by' => $this->user->id,
    ]);
});

test('impossible de supprimer un pointage déjà validé', function () {
    Event::fake();

    $entry = TimeEntry::factory()->create([
        'tenants_id' => $this->tenant->id,
        'status' => TimeEntryStatus::Approved->value
    ]);

    $response = $this->deleteJson("/api/hr/time-entries/{$entry->id}", [], $this->headers);

    $response->assertStatus(403);
    $this->assertDatabaseHas('time_entries', ['id' => $entry->id]);
});
