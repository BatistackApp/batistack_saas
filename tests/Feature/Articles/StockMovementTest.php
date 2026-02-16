<?php

use App\Enums\Articles\SerialNumberStatus;
use App\Enums\Articles\StockMovementType;
use App\Enums\Articles\TrackingType;
use App\Models\Articles\Article;
use App\Models\Articles\ArticleSerialNumber;
use App\Models\Articles\StockMovement;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\User;
use App\Notifications\Articles\LowStockAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);

    // Initialisation des rôles pour les notifications
    app()[PermissionRegistrar::class]->forgetCachedPermissions();
    Role::findOrCreate('logistics_manager', 'web');
    $this->user->assignRole('logistics_manager');
    Notification::fake();
});

describe("Flux de Stocks : Articles Standards (Quantité)", function () {

    it('enregistre une sortie chantier et décrémente le stock du bon dépôt', function () {
        $project = Project::factory()->create(['tenants_id' => $this->tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
        $article = Article::factory()->create([
            'tenants_id' => $this->tenant->id,
            'tracking_type' => TrackingType::Quantity,
            'cump_ht' => 50.00
        ]);

        $article->warehouses()->attach($warehouse->id, ['quantity' => 100]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/stock/movements', [
                'tenants_id' => $this->tenant->id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::Exit->value,
                'quantity' => 10,
                'project_id' => $project->id,
                'adjustement_type' => 'loss'
            ]);

        $response->assertStatus(201);

        $stock = $article->warehouses()->where('warehouse_id', $warehouse->id)->first()->pivot->quantity;
        expect((float) $stock)->toBe(90.0);
    });

    it('bloque un transfert si le stock source est insuffisant', function () {
        $from = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
        $to = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
        $article = Article::factory()->create(['tenants_id' => $this->tenant->id, 'tracking_type' => TrackingType::Quantity]);

        $article->warehouses()->attach($from->id, ['quantity' => 5]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/stock/movements', [
                'tenants_id' => $this->tenant->id,
                'article_id' => $article->id,
                'warehouse_id' => $from->id,
                'target_warehouse_id' => $to->id,
                'type' => StockMovementType::Transfer->value,
                'quantity' => 10,
            ]);

        $response->assertStatus(422);
    });
});

describe("Flux de Stocks : Articles Sérialisés (SN)", function () {

    it('crée un numéro de série lors d\'une entrée en stock d\'un article sérialisé', function () {
        $warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
        $article = Article::factory()->create([
            'tenants_id' => $this->tenant->id,
            'tracking_type' => TrackingType::SerialNumber,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/stock/movements', [
                'tenants_id' => $this->tenant->id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::Entry->value,
                'quantity' => 1,
                'serial_number' => 'SN-PERCEUSE-001',
                'unit_cost_ht' => 250.00,
                'purchase_date' => now()->format('Y-m-d'),
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('article_serial_numbers', [
            'serial_number' => 'SN-PERCEUSE-001',
            'article_id' => $article->id,
            'status' => SerialNumberStatus::InStock->value,
            'warehouse_id' => $warehouse->id
        ]);
    });

    it('interdit la sortie d\'un article sérialisé sans sélectionner de SN spécifique', function () {
        $article = Article::factory()->create(['tenants_id' => $this->tenant->id, 'tracking_type' => TrackingType::SerialNumber]);
        $warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/stock/movements', [
                'tenants_id' => $this->tenant->id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::Exit->value,
                'quantity' => 1,
                'project_id' => Project::factory()->create(['tenants_id' => $this->tenant->id])->id,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['serial_number_id']);
    });

    it('change le statut du SN en "Assigned" lors d\'une sortie chantier', function () {
        $warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
        $article = Article::factory()->create(['tenants_id' => $this->tenant->id, 'tracking_type' => TrackingType::SerialNumber]);
        $project = Project::factory()->create(['tenants_id' => $this->tenant->id]);

        // Initialisation du stock dans la table pivot pour passer la validation de quantité
        $article->warehouses()->attach($warehouse->id, ['quantity' => 1]);

        $sn = ArticleSerialNumber::create([
            'tenants_id' => $this->tenant->id,
            'article_id' => $article->id,
            'warehouse_id' => $warehouse->id,
            'serial_number' => 'HILTI-TE60-01',
            'status' => SerialNumberStatus::InStock
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/stock/movements', [
                'tenants_id' => $this->tenant->id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::Exit->value,
                'quantity' => 1,
                'serial_number_id' => $sn->id,
                'project_id' => $project->id,
                'adjustement_type' => 'loss'
            ]);

        $response->assertStatus(201);

        $sn->refresh();
        expect($sn->status)->toBe(SerialNumberStatus::Assigned)
            ->and($sn->warehouse_id)->toBeNull()
            ->and($sn->project_id)->toBe($project->id);
    });

    it('marque un SN comme "Lost" lors d\'un ajustement négatif', function () {
        $warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
        $article = Article::factory()->create(['tenants_id' => $this->tenant->id, 'tracking_type' => TrackingType::SerialNumber]);
        $article->warehouses()->attach($warehouse->id, ['quantity' => 1]);

        $sn = ArticleSerialNumber::create([
            'tenants_id' => $this->tenant->id,
            'article_id' => $article->id,
            'warehouse_id' => $warehouse->id,
            'serial_number' => 'LOST-MACHINE-99',
            'status' => SerialNumberStatus::InStock->value
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/stock/movements', [
                'tenants_id' => $this->tenant->id,
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::Adjustment->value,
                'adjustment_type' => \App\Enums\Articles\AdjustementType::Loss->value,
                'quantity' => -1,
                'serial_number_id' => $sn->id,
                'notes' => 'Matériel volé sur site'
            ]);

        $response->assertStatus(201);
        expect($sn->refresh()->status)->toBe(SerialNumberStatus::Lost);
    });
});

it('déclenche une notification d\'alerte quand le seuil critique est atteint', function () {
    $warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
    $article = Article::factory()->create([
        'tenants_id' => $this->tenant->id,
        'alert_stock' => 50,
        'tracking_type' => TrackingType::Quantity
    ]);

    $article->warehouses()->attach($warehouse->id, ['quantity' => 55]);

    $this->actingAs($this->user)->postJson('/api/articles/stock/movements', [
        'tenants_id' => $this->tenant->id,
        'article_id' => $article->id,
        'warehouse_id' => $warehouse->id,
        'type' => StockMovementType::Exit->value,
        'quantity' => 10,
        'project_id' => Project::factory()->create(['tenants_id' => $this->tenant->id])->id,
    ]);

    // Correction : assertSentTo au lieu de assertSentToAny
    Notification::assertSentTo($this->user, LowStockAlertNotification::class);
});
