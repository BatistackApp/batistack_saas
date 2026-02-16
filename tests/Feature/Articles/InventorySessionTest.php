<?php

use App\Enums\Articles\InventorySessionStatus;
use App\Enums\Articles\StockMovementType;
use App\Models\Articles\Article;
use App\Models\Articles\InventorySession;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    $this->tenant = Tenants::factory()->create();
    $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->warehouse = Warehouse::factory()->create(['tenants_id' => $this->tenant->id]);
    $this->user->givePermissionTo('inventory.manage');
});

describe("Workflow : Session d'Inventaire", function () {

    it('ouvre une session et capture le stock théorique', function () {
        // On prépare 2 articles en stock
        $articles = Article::factory()->count(2)->create(['tenants_id' => $this->tenant->id]);
        foreach ($articles as $article) {
            $article->warehouses()->attach($this->warehouse->id, ['quantity' => 10]);
        }

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/inventory/sessions', [
                'warehouse_id' => $this->warehouse->id,
                'notes' => 'Inventaire annuel 2026'
            ]);

        $response->assertStatus(201);

        $session = InventorySession::first();
        expect($session->status)->toBe(InventorySessionStatus::Open)
            ->and($session->lines)->toHaveCount(2)
            ->and((float)$session->lines->first()->theoretical_quantity)->toBe(10.0);
    });

    it('bloque les mouvements de stock standard quand le dépôt est gelé', function () {
        // Ouverture d'un inventaire
        $session = InventorySession::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'status' => InventorySessionStatus::Open,
            'tenants_id' => $this->tenant->id
        ]);

        $article = Article::factory()->create(['tenants_id' => $this->tenant->id]);

        // Tentative d'entrée en stock alors que le dépôt est en inventaire
        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/stock/movements', [
                'tenants_id' => $this->tenant->id,
                'article_id' => $article->id,
                'warehouse_id' => $this->warehouse->id,
                'type' => StockMovementType::Entry->value,
                'quantity' => 50,
                'unit_cost_ht' => 10
            ]);

        $response->assertStatus(422);
        $response->assertJsonPath('message', "Action impossible : Le dépôt {$this->warehouse->name} est gelé pour inventaire.");
    });


    it('valide la session et applique les ajustements de stock réels', function () {
        $article = Article::factory()->create(['tenants_id' => $this->tenant->id]);
        $article->warehouses()->attach($this->warehouse->id, ['quantity' => 10]);

        // Création d'une session avec un comptage déjà saisi (écart +5)
        $session = InventorySession::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'status' => InventorySessionStatus::Counting,
            'tenants_id' => $this->tenant->id,
            'opened_at' => now(),
            'created_by' => $this->user->id
        ]);

        $session->lines()->create([
            'article_id' => $article->id,
            'theoretical_quantity' => 10,
            'counted_quantity' => 15, // On en a trouvé 5 de plus
        ]);

        // Validation finale
        $response = $this->actingAs($this->user)
            ->postJson("/api/articles/inventory/sessions/{$session->id}/validate");


        $response->assertStatus(200);

        // Le stock doit être passé à 15
        $stock = $article->warehouses()->where('warehouse_id', $this->warehouse->id)->first()->pivot->quantity;
        expect((float)$stock)->toBe(15.0)
            ->and($session->refresh()->status)->toBe(InventorySessionStatus::Validated);
    });

    it('annule une session et lève le gel du dépôt', function () {
        $session = InventorySession::factory()->create([
            'warehouse_id' => $this->warehouse->id,
            'status' => InventorySessionStatus::Open,
            'tenants_id' => $this->tenant->id
        ]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/articles/inventory/sessions/{$session->id}");

        $response->assertStatus(200);
        expect($session->refresh()->status)->toBe(InventorySessionStatus::Cancelled);
    });
});
