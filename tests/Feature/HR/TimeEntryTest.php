<?php

use App\Enums\HR\TimeEntryStatus;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\HR\TimeEntry;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'tenant.settings.edit', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'payroll.manage', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'absences.create', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'time_entries.verify', 'guard_name' => 'web']);
    // Création du contexte de base (Tenant et Admin)
    $this->tenant = Tenants::factory()->create();
    $this->admin = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->admin->givePermissionTo('tenant.settings.edit', 'payroll.manage', 'absences.create', 'time_entries.verify');

    // Création des entités nécessaires
    $this->manager = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->employee = Employee::factory()->create(['tenants_id' => $this->tenant->id, 'manager_user_id' => $this->manager->id]);
    $this->project = Project::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->phase = ProjectPhase::factory()->create(['project_id' => $this->project->id]);

    $this->manager->givePermissionTo('payroll.manage', 'time_entries.verify');

    $this->actingAs($this->admin);

    Notification::fake();
    Queue::fake();
});

it('peut lister tous les pointages du tenant', function () {
    TimeEntry::factory()->count(3)->create([
        'tenants_id' => $this->tenant->id,
        'employee_id' => $this->employee->id,
        'project_id' => $this->project->id,
    ]);

    $response = $this->getJson(route('time-entries.index'));

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

it('peut lister les pointages d\'un employé avec filtres et résumé', function () {
    // Création de pointages sur différentes dates
    // 1. Un pointage le mois dernier
    TimeEntry::factory()->create([
        'tenants_id' => $this->tenant->id,
        'employee_id' => $this->employee->id,
        'date' => now()->subMonth(),
        'hours' => 8,
    ]);

    // 2. Deux pointages cette semaine (période cible)
    TimeEntry::factory()->create([
        'tenants_id' => $this->tenant->id,
        'employee_id' => $this->employee->id,
        'date' => now()->startOfWeek(),
        'hours' => 7.5,
        'has_meal_allowance' => true,
        'travel_time' => 1.0,
    ]);

    TimeEntry::factory()->create([
        'tenants_id' => $this->tenant->id,
        'employee_id' => $this->employee->id,
        'date' => now()->startOfWeek()->addDay(),
        'hours' => 8,
        'has_meal_allowance' => false,
        'travel_time' => 0.5,
    ]);

    $startDate = now()->startOfWeek()->format('Y-m-d');
    $endDate = now()->endOfWeek()->format('Y-m-d');

    $response = $this->getJson(route('employees.time-entries', [
        'employee' => $this->employee->id,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ]));

    //dd($response->json());

    $response->assertStatus(200)
        ->assertJsonCount(2, 'entries')
        ->assertJsonPath('summary.total_hours', 15.5)
        ->assertJsonPath('summary.total_travel_time', 1.5);
});

/**
 * Test de création
 */
it('peut enregistrer un nouveau pointage', function () {
    $payload = [
        'employee_id' => $this->employee->id,
        'project_id' => $this->project->id,
        'phase_id' => $this->phase->id,
        'date' => now()->format('Y-m-d'),
        'hours' => 8.5,
        'travel_time' => 1,
        'has_meal_allowance' => true,
        'notes' => 'Travaux de fondation',
    ];

    $response = $this->postJson(route('time-entries.store'), $payload);

    $response->assertStatus(201)
        ->assertJsonPath('data.hours', '8.50');

    $this->assertDatabaseHas('time_entries', [
        'employee_id' => $this->employee->id,
        'hours' => 8.5,
    ]);
});

/**
 * Test de vérification/approbation
 */
it('peut approuver un pointage et enregistre le validateur', function () {
    $entry = TimeEntry::factory()->create([
        'tenants_id' => $this->tenant->id,
        'status' => TimeEntryStatus::Submitted->value,
    ]);

    $response = $this->actingAs($this->manager)->patchJson(route('time-entries.verify', $entry), [
        'status' => TimeEntryStatus::Verified->value,
        'notes' => 'RAS, validé.',
    ]);

    $response = $this->actingAs($this->admin)->patchJson(route('time-entries.verify', $entry), [
        'status' => TimeEntryStatus::Approved->value,
        'notes' => 'RAS, validé.',
    ]);

    $response->assertStatus(200);

    $this->assertDatabaseHas('time_entries', [
        'id' => $entry->id,
        'status' => TimeEntryStatus::Approved->value,
        'verified_by' => $this->manager->id, // Vérifie que l'admin connecté est le validateur
    ]);
});

/**
 * Test de sécurité sur la suppression
 */
it('ne peut pas supprimer un pointage déjà approuvé', function () {
    $entry = TimeEntry::factory()->create([
        'tenants_id' => $this->tenant->id,
        'status' => TimeEntryStatus::Approved->value,
    ]);

    $response = $this->deleteJson(route('time-entries.destroy', $entry));

    $response->assertStatus(422)
        ->assertJsonStructure(['error']);

    $this->assertDatabaseHas('time_entries', ['id' => $entry->id]);
});

it('peut supprimer un pointage en mode brouillon', function () {
    $entry = TimeEntry::factory()->create([
        'tenants_id' => $this->tenant->id,
        'status' => TimeEntryStatus::Draft->value,
    ]);

    $response = $this->deleteJson(route('time-entries.destroy', $entry));

    $response->assertStatus(200);
    $this->assertDatabaseMissing('time_entries', ['id' => $entry->id]);
});
