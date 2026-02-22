<?php

use App\Enums\Expense\ExpenseStatus;
use App\Models\Core\Tenants;
use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseItem;
use App\Models\Expense\ExpenseReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');
    Queue::fake();
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

    // Setup Tenant A
    $this->tenantA = Tenants::factory()->create();
    $this->userA = User::factory()->create(['tenants_id' => $this->tenantA->id]);
    $this->adminA = User::factory()->create(['tenants_id' => $this->tenantA->id]);
    $this->adminA->assignRole('tenant_admin');

    $this->userA->givePermissionTo('tenant.expenses.manage');

    // Setup Tenant B (pour tester l'isolation)
    $this->tenantB = Tenants::factory()->create();
    $this->userB = User::factory()->create(['tenants_id' => $this->tenantB->id]);

    $this->category = ExpenseCategory::factory()->create([
        'tenants_id' => $this->tenantA->id,
        'name' => 'Transport',
        'requires_distance' => true,
    ]);
});

/**
 * TESTS DE SÉCURITÉ & MULTI-TENANCY
 */
test('un utilisateur ne peut pas voir les notes de frais d\'un autre tenant', function () {
    $reportB = ExpenseReport::factory()->create([
        'tenants_id' => $this->tenantB->id,
        'user_id' => $this->userB->id,
        'label' => 'Secret de Tenant B',
    ]);

    $this->actingAs($this->userA)
        ->getJson(route('expenses.reports.index'))
        ->assertOk()
        ->assertJsonMissing(['label' => 'Secret de Tenant B']);
});

test('un utilisateur ne peut pas modifier un rapport qui ne lui appartient pas (autre tenant)', function () {
    $reportB = ExpenseReport::factory()->create([
        'tenants_id' => $this->tenantB->id,
        'user_id' => $this->userB->id,
    ]);

    // Doit retourner 404 car le scope global filtre par tenant, rendant le rapport invisible
    $this->actingAs($this->userA)
        ->deleteJson("/api/expense/expense-reports/{$reportB->id}")
        ->assertStatus(404);
});

/**
 * TESTS DE FLUX (WORKFLOW)
 */
test('une note de frais passe en statut soumis et bloque les modifications', function () {
    $expense_report = ExpenseReport::factory()->create([
        'tenants_id' => $this->tenantA->id,
        'user_id' => $this->userA->id,
        'status' => ExpenseStatus::Draft,
    ]);

    // On ajoute une ligne pour pouvoir soumettre
    ExpenseItem::factory()->create(['expense_report_id' => $expense_report->id, 'amount_ttc' => 50]);

    $response = $this->actingAs($this->userA)
        ->postJson(route('expenses.reports.submit', $expense_report->id));

    expect($expense_report->refresh()->status)->toBe(ExpenseStatus::Submitted);

    // Tentative de suppression d'une ligne après soumission -> Doit échouer
    $item = $expense_report->items->first();
    $this->actingAs($this->userA)
        ->deleteJson(route('expenses.items.destroy', $item->id))
        ->assertStatus(422);
});

/**
 * TESTS DE CALCULS MÉTIER (IK vs Standard)
 */
test('le système calcule automatiquement le montant pour les frais kilométriques', function () {
    $this->userA->givePermissionTo('tenant.expenses.manage');
    $report = ExpenseReport::factory()->create([
        'tenants_id' => $this->tenantA->id,
        'user_id' => $this->userA->id,
    ]);

    $response = $this->actingAs($this->userA)
        ->postJson(route('expenses.items.store'), [
            'expense_report_id' => $report->id,
            'expense_category_id' => $this->category->id,
            'date' => now()->format('Y-m-d'),
            'description' => 'Trajet Chantier A',
            'is_mileage' => true,
            'distance_km' => 100,
            'vehicle_power' => 5,
        ]);

    $response->assertStatus(201);

    // 100km * 0.60 = 60.00
    $this->assertDatabaseHas('expense_items', [
        'expense_report_id' => $report->id,
        'amount_ttc' => 60.00,
    ]);

    // On vérifie le total du rapport (mis à jour par l'Observer)
    expect((float) $report->refresh()->amount_ttc)->toBe(60.00);
});

test('un justificatif est correctement stocké et lié à la ligne de frais', function () {
    $this->userA->givePermissionTo('tenant.expenses.manage');
    $report = ExpenseReport::factory()->create(['user_id' => $this->userA->id]);
    $file = UploadedFile::fake()->image('facture_resto.jpg');

    $response = $this->actingAs($this->userA)
        ->postJson(route('expenses.items.store'), [
            'expense_report_id' => $report->id,
            'expense_category_id' => $this->category->id,
            'date' => now()->format('Y-m-d'),
            'description' => 'Déjeuner client',
            'amount_ttc' => 45.50,
            'tax_rate' => 10,
            'receipt_path' => $file,
        ]);

    $response->assertStatus(201);

    $item = ExpenseItem::first();
    expect($item->receipt_path)->not->toBeNull();
    Storage::disk('public')->assertExists($item->receipt_path);
});

/**
 * TESTS D'APPROBATION
 */
test('l\'approbation déclenche l\'imputation comptable', function () {
    $report = ExpenseReport::factory()->create([
        'tenants_id' => $this->tenantA->id,
        'status' => ExpenseStatus::Draft,
        'user_id' => $this->userA->id,
    ]);
    ExpenseItem::factory()->create(['expense_report_id' => $report->id, 'amount_ttc' => 100]);

    $report->update(['status' => ExpenseStatus::Submitted]);

    // On donne la permission de valider à l'admin (simulé par le rôle ici)
    $this->actingAs($this->adminA)
        ->patchJson(route('expenses.reports.update-status', $report), [
            'status' => ExpenseStatus::Approved,
        ])
        ->assertOk();

    expect($report->refresh()->status)->toBe(ExpenseStatus::Approved);
});

test('le rejet d une note nécessite obligatoirement un motif', function () {
    $report = ExpenseReport::factory()->create([
        'tenants_id' => $this->tenantA->id,
        'status' => ExpenseStatus::Submitted,
    ]);

    $this->actingAs($this->adminA)
        ->patchJson(route('expenses.reports.update-status', $report), [
            'status' => ExpenseStatus::Rejected->value,
            'reason' => '', // Motif vide
        ])
        ->assertStatus(422)
        ->assertJsonValidationErrors(['reason']);
});

test('l ajout d une ligne met à jour automatiquement le total du rapport via l observer', function () {
    $report = ExpenseReport::factory()->create(['tenants_id' => $this->tenantA->id]);

    $this->actingAs($this->userA)
        ->postJson(route('expenses.items.store'), [
            'expense_report_id' => $report->id,
            'expense_category_id' => $this->category->id,
            'date' => now()->toDateString(),
            'description' => 'Achat outillage',
            'amount_ttc' => 120,
            'tax_rate' => 20,
        ])
        ->assertStatus(201);

    expect((float) $report->refresh()->amount_ttc)->toBe(120.0);
});
