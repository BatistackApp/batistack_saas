<?php

use App\Enums\Expense\ExpenseStatus;
use App\Jobs\Expense\ProcessChantierImputationJob;
use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = \App\Models\Core\Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->category = ExpenseCategory::factory()->create(['tenants_id' => $this->user->tenants_id]);
    Storage::fake('public');
    Queue::fake();
});

test('un employé peut créer un brouillon de note de frais', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('expense-reports.store'), [
            'label' => 'Frais Déplacement Paris',
        ]);

    $response->assertStatus(201)
        ->assertJsonPath('status', 'draft');

    $this->assertDatabaseHas('expense_reports', [
        'label' => 'Frais Déplacement Paris',
        'user_id' => $this->user->id
    ]);
});

test('un employé peut ajouter un item avec un justificatif à son brouillon', function () {
    $report = ExpenseReport::factory()->create(['user_id' => $this->user->id]);
    $file = UploadedFile::fake()->image('ticket.jpg');

    $response = $this->actingAs($this->user)
        ->postJson(route('expense-items.store'), [
            'expense_report_id' => $report->id,
            'expense_category_id' => $this->category->id,
            'date' => now()->format('Y-m-d'),
            'description' => 'Déjeuner client',
            'amount_ttc' => 50,
            'tax_rate' => 20,
            'receipt_path' => $file
        ]);

    $response->assertStatus(201);

    // Vérifier que le fichier est stocké
    Storage::disk('public')->assertExists('receipts/' . $this->user->id . '/' . $file->getFilename());

    // Vérifier que l'Observer a mis à jour le total du rapport
    expect($report->refresh()->amount_ttc)->toBe(50);
});

test('une note validée déclenche le job d\'imputation chantier', function () {
    $validator = User::factory()->create(['tenants_id' => $this->tenant->id]);
    // Créer la permission si elle n'existe pas
    $permission = \Spatie\Permission\Models\Permission::firstOrCreate(
        ['name' => 'tenant.expenses.validate', 'guard_name' => 'web']
    );
    $validator->givePermissionTo($permission);

    $report = ExpenseReport::factory()->create([
        'user_id' => $this->user->id,
        'status' => ExpenseStatus::Submitted
    ]);

    $response = $this->actingAs($validator)
        ->patchJson(route('expense-reports.update-status', $report), [
            'status' => ExpenseStatus::Approved->value
        ]);

    $response->assertOk();

    expect($report->refresh()->status)->toBe(ExpenseStatus::Approved);

    // Vérifier que le Job a été envoyé en file d'attente
    Queue::assertPushed(ProcessChantierImputationJob::class, function ($job) use ($report) {
        return $job->report->id === $report->id;
    });
});

test('un employé ne peut pas modifier une note déjà soumise', function () {
    $report = ExpenseReport::factory()->create([
        'user_id' => $this->user->id,
        'status' => ExpenseStatus::Submitted
    ]);

    $response = $this->actingAs($this->user)
        ->putJson(route('expense-reports.update', $report), [
            'label' => 'Tentative de fraude'
        ]);

    $response->assertStatus(403); // Via l'authorize() de la FormRequest
});
