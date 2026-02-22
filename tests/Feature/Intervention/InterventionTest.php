<?php

use App\Enums\HR\TimeEntryStatus;
use App\Enums\Intervention\BillingType;
use App\Enums\Intervention\InterventionStatus;
use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\Intervention\Intervention;
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
        'name' => 'Camion Tech 01'
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
 * Test de création et d'affectation automatique du dépôt
 */
test('une intervention peut être créée sans dépôt et l\'observer assigne le dépôt mobile du créateur', function () {
    $payload = [
        'customer_id' => $this->customer->id,
        'label' => 'Réparation fuite évier',
        'planned_at' => now()->addDay()->format('Y-m-d H:i:s'),
        'billing_type' => BillingType::Regie->value,
        'description' => 'Test de création',
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.store'), $payload);

    $response->assertStatus(201);

    $intervention = Intervention::first();
    // L'observer doit avoir trouvé le dépôt mobile du technicien lié à l'utilisateur
    expect($intervention->warehouse_id)->toBe($this->mobileWarehouse->id)
        ->and($intervention->status)->toBe(InterventionStatus::Planned);
});

/**
 * Test du démarrage technique
 */
test('on peut démarrer une intervention planifiée', function () {
    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'status' => InterventionStatus::Planned,
        'customer_id' => $this->customer->id
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.start', $intervention));


    $response->assertStatus(200);
    expect($intervention->fresh()->status)->toBe(InterventionStatus::InProgress);
});

/**
 * Test de l'ajout de matériel et calcul de marge en temps réel
 */
test('l\'ajout d\'un article met à jour la valorisation de l\'intervention', function () {
    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'status' => InterventionStatus::InProgress,
    ]);

    $payload = [
        'article_id' => $this->article->id,
        'label' => $this->article->name,
        'quantity' => 2,
        'unit_price_ht' => 50.00, // Saisie manuelle du prix de vente
        'is_billable' => true,
    ];

    $this->actingAs($this->user)
        ->postJson(route('interventions.items.store', $intervention), $payload);

    $intervention->refresh();

    // Vente : 2 * 50 = 100
    // Coût : 2 * 20 (CUMP) = 40
    // Marge : 60
    expect((float)$intervention->amount_ht)->toBe(100.0)
        ->and((float)$intervention->margin_ht)->toBe(60.0);
});

/**
 * Test de clôture avec Bon d'Attachement
 */
test('la clôture exige un rapport, une signature et génère les écritures RH/Stock', function () {
    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'status' => InterventionStatus::InProgress,
        'warehouse_id' => $this->mobileWarehouse->id,
        'project_id' => Project::factory(),
    ]);

    // Ajout d'un technicien via le pivot
    $intervention->technicians()->attach($this->technician->id, ['hours_spent' => 0]);

    $payload = [
        'report_notes' => 'Remplacement de la vanne effectué. Test de pression OK.',
        'client_signature' => 'data:image/png;base64,fake_signature_data',
        'completed_at' => now()->format('Y-m-d H:i:s'),
        'technicians' => [
            [
                'employee_id' => $this->technician->id,
                'hours_spent' => 3.5 // Saisie des heures à la clôture
            ]
        ]
    ];

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.complete', $intervention), $payload);

    $response->assertStatus(200);
    $intervention->refresh();

    // 1. Vérification du statut
    expect($intervention->status)->toBe(InterventionStatus::Completed)
        ->and($intervention->report_notes)->toBe($payload['report_notes']);

    // 2. Vérification du pointage RH (TimeEntry)
    $this->assertDatabaseHas('time_entries', [
        'employee_id' => $this->technician->id,
        'hours' => 3.5,
        'status' => TimeEntryStatus::Submitted->value
    ]);

    // 3. Vérification de la rentabilité finale
    // Coût Main d'oeuvre : 3.5h * 50€ = 175€
    // Si pas d'articles, marge = -175€
    expect((float)$intervention->amount_cost_ht)->toBe(175.0)
        ->and((float)$intervention->margin_ht)->toBe(-175.0);
});

/**
 * Test de sécurité : blocage sur client non-conforme
 */
test('on ne peut pas démarrer une intervention si le projet client est suspendu', function () {
    // On simule un projet dont le client est bloqué
    $project = Project::factory()->create(['tenants_id' => $this->tenantId]);
    $this->customer->update(['status' => \App\Enums\Tiers\TierStatus::Suspended]);

    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'customer_id' => $this->customer->id,
        'project_id' => $project->id,
        'status' => InterventionStatus::Planned,
    ]);

    // Le service ProjectManagementService (injecté dans le Workflow) devrait lever une exception
    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.start', $intervention));

    $response->assertStatus(422);
    $response->assertJsonPath('error', 'Client non conforme ou suspendu administrativement.');
});
