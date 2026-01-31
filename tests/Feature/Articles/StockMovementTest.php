<?php

use App\Enums\Articles\StockMovementType;
use App\Models\Articles\Article;
use App\Models\Articles\StockMovement;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\User;
use App\Notifications\Articles\LowStockAlertNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("API: Stock Mouvement Controller", function () {
    it('enregistre une sortie chantier et décrémente le stock du bon dépôt', function () {
        // On crée un tenant commun pour isoler le test
        $tenant = Tenants::factory()->create();
        $user = User::factory()->create(['tenants_id' => $tenant->id]);

        // On s'assure que toutes les entités appartiennent au même tenant
        $project = Project::factory()->create(['tenants_id' => $tenant->id]);
        $warehouse = Warehouse::factory()->create(['tenants_id' => $tenant->id]);
        $article = Article::factory()->create([
            'tenants_id' => $tenant->id,
            'cump_ht' => 50.00
        ]);

        // Stock initial : 100 unités
        $article->warehouses()->attach($warehouse->id, ['quantity' => 100]);

        $response = $this->actingAs($user)
            ->postJson('/api/stock/movements', [
                'article_id' => $article->id,
                'warehouse_id' => $warehouse->id,
                'type' => StockMovementType::Exit->value,
                'quantity' => 10,
                'project_id' => $project->id,
            ]);

        // Si ça échoue encore, décommentez la ligne suivante pour voir l'erreur de validation :
        // $response->dump();

        $response->assertStatus(201);

        // Vérification du stock restant
        $stock = $article->warehouses()->where('warehouse_id', $warehouse->id)->first()->pivot->quantity;
        expect((float) $stock)->toBe(90.0);

        // Vérification de l'imputation analytique
        $movement = StockMovement::where('article_id', $article->id)->first();
        expect($movement->unit_cost_ht)->toBe("50.00")
            ->and($movement->project_id)->toBe($project->id)
            ->and($movement->tenants_id)->toBe($tenant->id);
    });

    it('bloque un transfert si le stock source est insuffisant', function () {
        $tenant = Tenants::factory()->create();
        $user = User::factory()->create(['tenants_id' => $tenant->id]);

        $from = Warehouse::factory()->create(['tenants_id' => $tenant->id, 'name' => 'Dépôt A']);
        $to = Warehouse::factory()->create(['tenants_id' => $tenant->id, 'name' => 'Dépôt B']);
        $article = Article::factory()->create(['tenants_id' => $tenant->id]);

        $article->warehouses()->attach($from->id, ['quantity' => 5]);

        $response = $this->actingAs($user)
            ->postJson('/api/stock/movements', [
                'article_id' => $article->id,
                'warehouse_id' => $from->id,
                'target_warehouse_id' => $to->id,
                'type' => StockMovementType::Transfer->value,
                'quantity' => 10, // Trop élevé
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', "Transfert impossible : Stock source insuffisant.");
    });

    it('déclenche une notification d\'alerte quand le seuil critique est atteint', function () {
        Notification::fake();

        $tenant = Tenants::factory()->create();
        $user = User::factory()->create(['tenants_id' => $tenant->id]);

        // Simuler le rôle pour passer la garde de notification
        // Note: Assurez-vous que Spatie Permissions est configuré ou mockez la logique de rôle
        $user->assignRole('logistics_manager');

        $warehouse = Warehouse::factory()->create(['tenants_id' => $tenant->id]);
        $article = Article::factory()->create([
            'tenants_id' => $tenant->id,
            'alert_stock' => 50,
        ]);

        $article->warehouses()->attach($warehouse->id, ['quantity' => 55]);

        $this->actingAs($user)->postJson('/api/stock/movements', [
            'article_id' => $article->id,
            'warehouse_id' => $warehouse->id,
            'type' => StockMovementType::Exit->value,
            'quantity' => 10,
            'project_id' => Project::factory()->create(['tenants_id' => $tenant->id])->id,
        ]);

        Notification::assertSentTo(
            $user,
            LowStockAlertNotification::class
        );
    });
});
