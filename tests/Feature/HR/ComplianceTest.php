<?php

use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeSkill;
use App\Models\HR\Skill;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    $this->tenant = Tenants::factory()->create();
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

    Role::findOrCreate('hr_manager', 'web');

    // Création d'un admin
    $this->admin = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->admin->assignRole('tenant_admin');

    // Création d'un RH
    $this->hrManager = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->hrManager->assignRole('hr_manager');
});

test('un administrateur peut créer un type d\'habilitation', function () {
    $response = $this->actingAs($this->admin)
        ->postJson('/api/hr/skills', [
            'name' => 'CACES R482',
            'type' => \App\Enums\HR\SkillType::Habilitation->value,
            'description' => 'Certificat de conduite en sécurité',
            'requires_expiry' => true
        ]);

    $response->assertStatus(201);
    expect(Skill::where('name', 'CACES R482')->exists())->toBeTrue();
});

test('un utilisateur non autorisé ne peut pas créer d\'habilitation', function () {
    $user = User::factory()->create(['tenants_id' => $this->tenant->id]);

    $response = $this->actingAs($user)
        ->postJson('/api/hr/skills', [
            'name' => 'Habilitation Électrique'
        ]);

    $response->assertStatus(403);
});

/**
 * TESTS SUR L'AFFECTATION AUX EMPLOYÉS (EmployeeSkills)
 */

test('un gestionnaire RH peut affecter une habilitation avec un document', function () {
    $employee = Employee::factory()->create(['tenants_id' => $this->tenant->id]);
    $skill = Skill::factory()->create();
    $file = UploadedFile::fake()->create('diplome.pdf', 1024);

    // Note: Utilisation de 'document_path' comme clé d'input selon votre StoreEmployeeSkillRequest
    $response = $this->actingAs($this->hrManager)
        ->postJson('/api/hr/employee-skills', [
            'employee_id' => $employee->id,
            'skill_id' => $skill->id,
            'issue_date' => now()->subDays(5)->format('Y-m-d'),
            'expiry_date' => now()->addYear()->format('Y-m-d'),
            'reference_number' => 'REF-12345',
            'document_path' => $file,
        ]);

    $response->assertStatus(201);

    $assignment = EmployeeSkill::first();
    expect($assignment->document_path)->not->toBeNull();
    Storage::disk('public')->assertExists($assignment->document_path);
});

test('la création d\'une affectation échoue sans document justificatif', function () {
    $employee = Employee::factory()->create(['tenants_id' => $this->tenant->id]);
    $skill = Skill::factory()->create();

    $response = $this->actingAs($this->hrManager)
        ->postJson('/api/hr/employee-skills', [
            'employee_id' => $employee->id,
            'skill_id' => $skill->id,
            'issue_date' => now()->format('Y-m-d'),
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['document_path']);
});

test('la date d\'expiration doit être après la date d\'émission', function () {
    $employee = Employee::factory()->create(['tenants_id' => $this->tenant->id]);
    $skill = Skill::factory()->create();
    $file = UploadedFile::fake()->create('doc.png', 500);

    $response = $this->actingAs($this->hrManager)
        ->postJson('/api/hr/employee-skills', [
            'employee_id' => $employee->id,
            'skill_id' => $skill->id,
            'issue_date' => now()->format('Y-m-d'),
            'expiry_date' => now()->subDay()->format('Y-m-d'), // Hier !
            'document_path' => $file,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['expiry_date']);
});

test('la suppression d\'une affectation supprime aussi le fichier physique', function () {
    $file = UploadedFile::fake()->create('to_delete.pdf', 100);
    $path = Storage::disk('public')->put('hr/compliance', $file);

    $assignment = EmployeeSkill::factory()->create([
        'document_path' => $path
    ]);

    $this->actingAs($this->admin)
        ->deleteJson("/api/hr/employee-skills/{$assignment->id}")
        ->assertStatus(200);

    expect(EmployeeSkill::find($assignment->id))->toBeNull();
    Storage::disk('public')->assertMissing($path);
});
