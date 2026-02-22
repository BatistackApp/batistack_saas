<?php

use App\Enums\Articles\StockMovementType;
use App\Enums\HR\TimeEntryStatus;
use App\Enums\Intervention\BillingType;
use App\Enums\Intervention\InterventionStatus;
use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\Intervention\Intervention;
use App\Models\Intervention\InterventionItem;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Initialisation des permissions
    Permission::firstOrCreate(['name' => 'intervention.create', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'intervention.update', 'guard_name' => 'web']);
    Permission::firstOrCreate(['name' => 'intervention.manage', 'guard_name' => 'web']);

    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo(['intervention.create', 'intervention.update', 'intervention.manage']);

    $this->tenantId = $this->tenant->id;

    // Création du contexte métier
    $this->customer = Tiers::factory()->create(['tenants_id' => $this->tenantId]);

    // Un dépôt pour le technicien (Dépôt Mobile)
    $this->mobileWarehouse = Warehouse::factory()->create([
        'tenants_id' => $this->tenantId,
        'name' => 'Camion Tech 01',
    ]);

    $this->technician = Employee::factory()->create([
        'tenants_id' => $this->tenantId,
        'user_id' => $this->user->id,
        'hourly_cost_charged' => 50.00, // Coût horaire chargé
    ]);

    // On assume que l'Employee possède un lien vers un dépôt par défaut (recommandation étape 5)
    $this->technician->update(['default_warehouse_id' => $this->mobileWarehouse->id]);

    $this->article = Article::factory()->create([
        'tenants_id' => $this->tenantId,
        'cump_ht' => 20.00,
        'sale_price_ht' => 45.00,
    ]);

    Queue::fake();
});

/**
 * --- TESTS CRUD & INITIALISATION ---
 */
test('on peut lister les interventions du tenant', function () {
    Intervention::factory(3)->create(['tenants_id' => $this->tenantId]);

    $response = $this->actingAs($this->user)
        ->getJson(route('interventions.index'));

    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('on peut créer une intervention planifiée avec des techniciens', function () {
    $payload = [
        'customer_id' => $this->customer->id,
        'warehouse_id' => $this->mobileWarehouse->id,
        'label' => 'Maintenance préventive Groupe Électrogène',
        'planned_at' => now()->addDays(2)->toDateTimeString(),
        'billing_type' => BillingType::Regie->value,
        'technician_ids' => [$this->technician->id],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.store'), $payload);

    $response->assertStatus(201);

    $this->assertDatabaseHas('interventions', [
        'label' => 'Maintenance préventive Groupe Électrogène',
        'status' => InterventionStatus::Planned->value,
    ]);

    $intervention = Intervention::latest()->first();
    expect($intervention->technicians)->toHaveCount(1);
});

/**
 * --- TESTS DE WORKFLOW (ETATS) ---
 */
test('on peut démarrer une intervention planifiée', function () {
    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'status' => InterventionStatus::Planned,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.start', $intervention));

    $response->assertStatus(200);
    expect($intervention->fresh()->status)->toBe(InterventionStatus::InProgress)
        ->and($intervention->fresh()->started_at)->not->toBeNull();
});

test('la clôture génère les sorties de stock, les pointages RH et calcule la marge réelle', function () {
    // Préparation d'une intervention en cours
    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'warehouse_id' => $this->mobileWarehouse->id,
        'status' => InterventionStatus::InProgress,
    ]);

    // Ajout d'un article consommé (InterventionItem)
    InterventionItem::factory()->create([
        'intervention_id' => $intervention->id,
        'article_id' => $this->article->id,
        'quantity' => 2, // 2 * 120€ vente = 240€ / 2 * 50€ coût = 100€
        'unit_cost_ht' => 50.00,
        'unit_price_ht' => 120.00,
        'total_ht' => 240.00,
        'is_billable' => true,
    ]);

    // Affectation initiale du technicien
    $intervention->technicians()->attach($this->technician->id, ['hours_spent' => 0]);

    // Payload de clôture (Rapport technique + Heures finales)
    $payload = [
        'report_notes' => 'Remplacement des filtres effectué. Test de charge OK.',
        'completed_at' => now()->toDateTimeString(),
        'client_signature' => 'data:image/png;base64,signature_virtuelle_btp',
        'technicians' => [
            ['employee_id' => $this->technician->id, 'hours_spent' => 3.0], // 3h * 45€ = 135€ coût MO
        ],
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.complete', $intervention), $payload);

    $response->assertStatus(200);

    $intervention->refresh();

    // 1. Vérification du changement de statut
    expect($intervention->status)->toBe(InterventionStatus::Completed);

    // 2. Vérification des impacts Stocks
    $this->assertDatabaseHas('stock_movements', [
        'article_id' => $this->article->id,
        'quantity' => 2,
        'type' => StockMovementType::Exit->value,
    ]);

    // 3. Vérification des impacts RH (Pointage)
    $this->assertDatabaseHas('time_entries', [
        'employee_id' => $this->technician->id,
        'hours' => 3.0,
        'status' => TimeEntryStatus::Submitted->value,
    ]);

    // 4. Vérification du calcul financier (FinancialService)
    // Vente HT = 240€
    // Coût Matériel = 100€
    // Coût Main d'oeuvre = 135€
    // Total Coût = 235€
    // Marge = 240 - 235 = 5€
    expect((float) $intervention->amount_ht)->toBe(240.0)
        ->and((float) $intervention->amount_cost_ht)->toBe(235.0)
        ->and((float) $intervention->margin_ht)->toBe(5.0);
});

/**
 * --- TESTS DE SÉCURITÉ & RÈGLES MÉTIER ---
 */
test('on ne peut pas supprimer une intervention si elle est déjà commencée', function () {
    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'status' => InterventionStatus::InProgress,
    ]);

    $response = $this->actingAs($this->user)
        ->deleteJson(route('interventions.destroy', $intervention));

    $response->assertStatus(500);
    $this->assertDatabaseHas('interventions', ['id' => $intervention->id]);
});

test('on ne peut pas démarrer une intervention si le client est suspendu administrativement', function () {
    $this->customer->update(['status' => \App\Enums\Tiers\TierStatus::Suspended]);

    $project = Project::factory()->create([
        'tenants_id' => $this->tenantId,
        'customer_id' => $this->customer->id,
    ]);

    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'customer_id' => $this->customer->id,
        'project_id' => $project->id,
        'status' => InterventionStatus::Planned,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.start', $intervention));

    // Doit être bloqué par le ComplianceException du WorkflowService
    $response->assertStatus(422)
        ->assertJsonPath('error', 'Client non conforme ou suspendu administrativement.');
});
