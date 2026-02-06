<?php

use App\Enums\Intervention\BillingType;
use App\Enums\Intervention\InterventionStatus;
use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\Intervention\Intervention;
use App\Models\Tiers\Tiers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'intervention.manage', 'guard_name' => 'web']);
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo('intervention.manage');
    $this->tenantId = $this->user->tenants_id;

    $this->customer = Tiers::factory()->create(['tenants_id' => $this->tenantId]);
    $this->warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenantId]);
    $this->article = Article::factory()->create([
        'tenants_id' => $this->tenantId,
        'cump_ht' => 50.00,
        'sale_price_ht' => 100.00
    ]);

    Queue::fake();
});

test('on peut créer une intervention planifiée', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.store'), [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'label' => 'Maintenance Chaudière',
            'planned_at' => now()->addDays(1)->toDateTimeString(),
            'billing_type' => BillingType::Regie->value,
        ]);

    $response->assertStatus(201);
    $this->assertDatabaseHas('interventions', [
        'label' => 'Maintenance Chaudière',
        'status' => InterventionStatus::Planned->value,
    ]);
});

test('on peut ajouter du matériel et la marge se calcule automatiquement', function () {
    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'status' => InterventionStatus::InProgress,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.items.store', $intervention), [
            'article_id' => $this->article->id,
            'label' => $this->article->name,
            'quantity' => 2,
            'unit_price_ht' => 120.00, // Prix forcé pour le test
            'is_billable' => true,
        ]);

    $response->assertStatus(201);

    $intervention->refresh();
    // Vente : 2 * 120 = 240
    // Coût : 2 * 50 = 100
    // Marge : 240 - 100 = 140
    expect((float)$intervention->amount_ht)->toBe(240.0)
        ->and((float)$intervention->margin_ht)->toBe(140.0);
});

test('la clôture d\'une intervention génère les pointages RH', function () {
    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'status' => InterventionStatus::InProgress,
    ]);

    $employee = Employee::factory()->create([
        'tenants_id' => $this->tenantId,
        'hourly_cost_charged' => 45.00
    ]);

    // Ajout d'un technicien via pivot
    $intervention->technicians()->attach($employee->id, ['hours_spent' => 4]);

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.complete', $intervention));

    $response->assertStatus(200);

    // Vérifier la création du pointage dans time_entries
    $this->assertDatabaseHas('time_entries', [
        'employee_id' => $employee->id,
        'hours' => 4.0,
        'status' => \App\Enums\HR\TimeEntryStatus::Submitted->value,
    ]);

    // Vérifier que la marge a pris en compte la main d'œuvre
    // Ici on n'a pas d'items, donc Marge = 0 - (4 * 45) = -180
    $intervention->refresh();
    expect((float)$intervention->margin_ht)->toBe(-180.0);
});

test('on ne peut pas démarrer une intervention pour un client suspendu', function () {
    $suspendedCustomer = Tiers::factory()->create([
        'tenants_id' => $this->tenantId,
        'status' => \App\Enums\Tiers\TierStatus::Suspended
    ]);

    $intervention = Intervention::factory()->create([
        'tenants_id' => $this->tenantId,
        'customer_id' => $suspendedCustomer->id,
        'status' => InterventionStatus::Planned,
    ]);

    $response = $this->actingAs($this->user)
        ->postJson(route('interventions.start', $intervention));

    $response->assertStatus(422);
    expect($response->json('error'))->toContain('Le client est suspendu');
});
