<?php

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\AbsenceType;
use App\Models\Core\Tenants;
use App\Models\HR\AbsenceRequest;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'tenant.settings.edit', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'payroll.manage', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'absences.create', 'guard_name' => 'web']);
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'absences.validate', 'guard_name' => 'web']);

    // Création du contexte de base (Tenant et Admin)
    $this->tenant = Tenants::factory()->create();
    $this->admin = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->admin->givePermissionTo('payroll.manage', 'absences.create', 'absences.validate');

    // Création des entités nécessaires
    $this->manager = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->employee = Employee::factory()->create([
        'tenants_id' => $this->tenant->id,
        'manager_user_id' => $this->manager->id,
        'user_id' => $this->admin->id, // L'admin est aussi l'employé pour les tests de soumission
    ]);

    $this->actingAs($this->admin);

    Notification::fake();
    Queue::fake();
    Storage::fake('public');
});

it('peut créer une demande d\'absence', function () {
    $payload = [
        'type' => AbsenceType::PaidLeave->value,
        'starts_at' => now()->addDays(5)->format('Y-m-d'),
        'ends_at' => now()->addDays(7)->format('Y-m-d'),
        'reason' => 'Vacances d\'été',
        'employee_id' => $this->employee->id,
    ];

    $response = $this->postJson(route('absences.store'), $payload);

    $response->assertStatus(201)
        ->assertJsonPath('data.type', AbsenceType::PaidLeave->value);

    $this->assertDatabaseHas('absence_requests', [
        'employee_id' => $this->employee->id,
        'type' => AbsenceType::PaidLeave->value,
        'status' => AbsenceRequestStatus::Pending->value,
    ]);
});

it('calcule correctement la durée en jours ouvrés via le service', function () {
    // Lundi au Vendredi (5 jours)
    $start = now()->next('Monday');
    $end = $start->copy()->addDays(4);

    $payload = [
        'type' => AbsenceType::PaidLeave->value,
        'starts_at' => $start->format('Y-m-d'),
        'ends_at' => $end->format('Y-m-d'),
        'employee_id' => $this->employee->id,
    ];

    $response = $this->postJson(route('absences.store'), $payload);

    $response->assertStatus(201)
        ->assertJsonPath('data.duration_days', '5.00');
});

it('gère le fichier de justification', function () {
    $file = UploadedFile::fake()->create('arret_maladie.pdf', 100);

    $payload = [
        'type' => AbsenceType::SickLeave->value,
        'starts_at' => now()->format('Y-m-d'),
        'ends_at' => now()->addDays(2)->format('Y-m-d'),
        'justification_file' => $file,
        'employee_id' => $this->employee->id,
    ];

    $response = $this->postJson(route('absences.store'), $payload);

    $response->assertStatus(201);

    $absence = AbsenceRequest::first();
    expect($absence->justification_path)->not->toBeNull();
    Storage::disk('public')->assertExists($absence->justification_path);
});

it('empêche la création de demande en conflit', function () {
    // Création d'une absence existante
    AbsenceRequest::factory()->create([
        'employee_id' => $this->employee->id,
        'starts_at' => now()->addDays(5),
        'ends_at' => now()->addDays(7),
        'status' => AbsenceRequestStatus::Approved,
    ]);

    // Tentative de création sur la même période
    $payload = [
        'type' => AbsenceType::PaidLeave->value,
        'starts_at' => now()->addDays(6)->format('Y-m-d'),
        'ends_at' => now()->addDays(8)->format('Y-m-d'),
        'employee_id' => $this->employee->id,
    ];

    $response = $this->postJson(route('absences.store'), $payload);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['starts_at']);
});

it('permet au manager de valider une demande', function () {
    $absence = AbsenceRequest::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => AbsenceRequestStatus::Pending,
    ]);

    // On se connecte en tant que manager (ou admin avec droits)
    $this->actingAs($this->admin);

    $response = $this->patchJson(route('absences.review', $absence), [
        'status' => AbsenceRequestStatus::Approved->value,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.status', AbsenceRequestStatus::Approved->value);

    $this->assertDatabaseHas('absence_requests', [
        'id' => $absence->id,
        'status' => AbsenceRequestStatus::Approved->value,
    ]);
});

it('ne peut pas supprimer une demande déjà traitée', function () {
    $absence = AbsenceRequest::factory()->create([
        'employee_id' => $this->employee->id,
        'status' => AbsenceRequestStatus::Approved->value,
    ]);

    $response = $this->deleteJson(route('absences.destroy', $absence));

    $response->assertStatus(422);
    $this->assertDatabaseHas('absence_requests', ['id' => $absence->id]);
});
