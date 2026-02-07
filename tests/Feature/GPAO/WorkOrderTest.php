<?php

use App\Enums\Articles\StockMovementType;
use App\Enums\GPAO\OperationStatus;
use App\Enums\GPAO\WorkOrderStatus;
use App\Models\Articles\Article;
use App\Models\Articles\Ouvrage;
use App\Models\Articles\Warehouse;
use App\Models\GPAO\WorkCenter;
use App\Models\GPAO\WorkOrder;
use App\Models\User;
use App\Notifications\GPAO\StockShortageNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'gpao.manage', 'guard_name' => 'web']);
    $this->tenant = \App\Models\Core\Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo(['gpao.manage']);
    $this->tenantsId = $this->user->tenants_id;

    $this->warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenantsId]);
    $this->article = Article::factory()->create([
        'tenants_id' => $this->tenantsId,
        'cump_ht' => 10.00,
        'total_stock' => 100
    ]);

    $this->article->warehouses()->attach($this->warehouse->id, [
        'quantity' => 100,
        'bin_location' => 'A1'
    ]);

    $this->ouvrage = Ouvrage::factory()->create(['tenants_id' => $this->tenantsId]);
    $this->ouvrage->components()->attach($this->article->id, ['quantity_needed' => 2]);

    $this->workCenter = WorkCenter::factory()->create(['tenants_id' => $this->tenantsId]);

    Queue::fake();
});

test('la création d\'un OF explose automatiquement la nomenclature', function () {
    $response = $this->actingAs($this->user)
        ->postJson(route('work-orders.store'), [
            'ouvrage_id' => $this->ouvrage->id,
            'warehouse_id' => $this->warehouse->id,
            'quantity_planned' => 5,
            'planned_start_at' => now()->addDay()->toDateTimeString(),
            'planned_end_at' => now()->addDays(5)->toDateTimeString(),
        ]);

    $response->assertStatus(201);

    $wo = WorkOrder::first();
    $this->assertCount(1, $wo->components);
    expect((float)$wo->components->first()->quantity_planned)->toBe(10.0);
});

test('le démarrage de la première opération déstocke les matières premières', function () {
    $wo = WorkOrder::factory()->create([
        'tenants_id' => $this->tenantsId,
        'status' => WorkOrderStatus::Planned,
        'ouvrage_id' => $this->ouvrage->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity_planned' => 5,
    ]);

    // Création du composant attendu par l'OF
    $wo->components()->create([
        'article_id' => $this->article->id,
        'label' => 'Matière',
        'quantity_planned' => 10,
        'unit_cost_ht' => 10
    ]);

    $op = $wo->operations()->create([
        'work_center_id' => $this->workCenter->id,
        'sequence' => 10,
        'label' => 'Débit',
        'status' => OperationStatus::Pending
    ]);

    $response = $this->actingAs($this->user)
        ->patchJson(route('gpao.operations.update-status', $op), [
            'status' => OperationStatus::Running->value
        ]);

    $response->assertStatus(200);

    // Vérifier que le mouvement de stock est bien présent (type 'exit' ou 'Issue')
    $this->assertDatabaseHas('stock_movements', [
        'article_id' => $this->article->id,
        'type' => StockMovementType::Exit->value,
        'quantity' => 10
    ]);
});

test('la finalisation d\'un OF avec quantité partielle recalcule le coût unitaire', function () {
    $wo = WorkOrder::factory()->create([
        'tenants_id' => $this->tenantsId,
        'status' => WorkOrderStatus::InProgress,
        'quantity_planned' => 10,
        'ouvrage_id' => $this->ouvrage->id,
        'warehouse_id' => $this->warehouse->id
    ]);

    // Coût matières : 100€ (pour les 10 prévus)
    $wo->components()->create([
        'article_id' => $this->article->id,
        'label' => 'Composant',
        'quantity_planned' => 20,
        'quantity_consumed' => 20,
        'unit_cost_ht' => 5.00
    ]);

    // Fabrication de seulement 8 unités (2 rebus)
    $response = $this->actingAs($this->user)
        ->postJson(route('work-orders.finalize', $wo), [
            'quantity_produced' => 8
        ]);

    $response->assertStatus(200);

    $wo->refresh();
    expect((float)$wo->quantity_produced)->toBe(8.0);

    // Le coût unitaire doit être 100 / 8 = 12.50€ (au lieu de 10€ théoriques)
    $this->assertDatabaseHas('stock_movements', [
        'ouvrage_id' => $this->ouvrage->id,
        'type' => StockMovementType::Entry->value,
        'quantity' => 8,
        'unit_cost_ht' => 12.50
    ]);
});

test('le passage au statut PLANNED déstocke les matières premières', function () {
    // 1. Création en statut DRAFT
    $wo = WorkOrder::factory()->create([
        'tenants_id' => $this->tenantsId,
        'status' => WorkOrderStatus::Draft,
        'ouvrage_id' => $this->ouvrage->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity_planned' => 5,
    ]);

    // Simuler l'explosion de nomenclature
    app(\App\Services\GPAO\ProductionOrchestrator::class)->initializeFromOuvrage($wo);

    // 2. Changement de statut vers PLANNED
    $response = $this->actingAs($this->user)
        ->patchJson(route('work-orders.update', $wo), [
            'status' => WorkOrderStatus::Planned->value
        ]);

    $response->assertStatus(200);

    // 3. Vérifier que le mouvement de stock (Issue) a été généré par l'Observer
    $this->assertDatabaseHas('stock_movements', [
        'article_id' => $this->article->id,
        'type' => StockMovementType::Exit->value,
        'quantity' => 10 // 5 unités * 2 composants par nomenclature
    ]);
});

test('on ne peut pas planifier si le stock est insuffisant', function () {
    $wo = WorkOrder::factory()->create([
        'tenants_id' => $this->tenantsId,
        'status' => WorkOrderStatus::Draft,
        'ouvrage_id' => $this->ouvrage->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity_planned' => 500, // Demande 1000 articles alors qu'on en a 100
    ]);

    app(\App\Services\GPAO\ProductionOrchestrator::class)->initializeFromOuvrage($wo);

    $response = $this->actingAs($this->user)
        ->patchJson(route('work-orders.update', $wo), [
            'status' => WorkOrderStatus::Planned->value
        ]);

    // L'Observer lève une InsufficientMaterialException via le service
    $response->assertStatus(422);
    $this->assertDatabaseMissing('stock_movements', ['quantity' => 1000]);
});

test('une notification est envoyée en cas de stock insuffisant lors du check', function () {
    Notification::fake();

    $wo = WorkOrder::factory()->create([
        'tenants_id' => $this->tenantsId,
        'ouvrage_id' => $this->ouvrage->id,
        'warehouse_id' => $this->warehouse->id,
        'quantity_planned' => 1000
    ]);

    $wo->components()->create([
        'article_id' => $this->article->id,
        'label' => 'Matière critique',
        'quantity_planned' => 5000,
        'unit_cost_ht' => 10
    ]);

    $service = app(\App\Services\GPAO\ProductionOrchestrator::class);
    $result = $service->validateStockAvailability($wo);

    expect($result)->toBeFalse();
    Notification::assertSentTo($this->user, StockShortageNotification::class);
});
