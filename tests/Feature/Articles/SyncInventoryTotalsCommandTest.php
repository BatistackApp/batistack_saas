<?php

use App\Enums\Articles\StockMovementType;
use App\Models\Articles\Article;
use App\Models\Articles\StockMovement;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

uses(RefreshDatabase::class);

describe("Command: Test de Synchronisation d'inventaire", function () {
    beforeEach(function () {
        $this->tenant = Tenants::factory()->create();
        $this->user = User::factory()->create(['tenants_id' => $this->tenant->id]);

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2.1 Définition des permissions granulaires par module
        $permissions = [
            // Chantiers
            'projects.view', 'projects.create', 'projects.edit', 'projects.delete', 'projects.manage_budget',
            // Stock
            'inventory.view', 'inventory.manage', 'inventory.audit',
            // Tiers
            'tiers.view', 'tiers.manage', 'tiers.compliance_validate',
            // Administration Tenant
            'tenant.users.manage', 'tenant.settings.edit',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }
        $logistics = Role::findOrCreate('logistics_manager', 'web');
        $logistics->givePermissionTo([
            'inventory.view', 'inventory.manage', 'inventory.audit',
            'projects.view', 'tiers.view',
        ]);
    });

    it('recalcule avec précision les stocks par dépôt et le total global', function () {
        // 1. Préparation des entités
        $warehouseA = Warehouse::factory()->create(['tenants_id' => $this->tenant->id, 'name' => 'Dépôt A']);
        $warehouseB = Warehouse::factory()->create(['tenants_id' => $this->tenant->id, 'name' => 'Dépôt B']);

        $article = Article::factory()->create([
            'tenants_id' => $this->tenant->id,
            'sku' => 'TEST-SYNC-01',
            'total_stock' => 0,
        ]);

        // Initialisation du pivot (la commande le fera, mais on simule un état existant erroné)
        $article->warehouses()->attach([
            $warehouseA->id => ['quantity' => 0],
            $warehouseB->id => ['quantity' => 0],
        ]);

        // 2. Création d'un historique de mouvements complexe
        // Dépôt A : +100 (Entry) + 10 (Return) - 20 (Exit) - 5 (Adj Perte) = 85 avant transfert
        StockMovement::create([
            'tenants_id' => $this->tenant->id, 'article_id' => $article->id, 'warehouse_id' => $warehouseA->id,
            'type' => StockMovementType::Entry, 'quantity' => 100, 'user_id' => $this->user->id,
        ]);

        StockMovement::create([
            'tenants_id' => $this->tenant->id, 'article_id' => $article->id, 'warehouse_id' => $warehouseA->id,
            'type' => StockMovementType::Return, 'quantity' => 10, 'user_id' => $this->user->id,
        ]);

        StockMovement::create([
            'tenants_id' => $this->tenant->id, 'article_id' => $article->id, 'warehouse_id' => $warehouseA->id,
            'type' => StockMovementType::Exit, 'quantity' => 20, 'user_id' => $this->user->id,
        ]);

        StockMovement::create([
            'tenants_id' => $this->tenant->id, 'article_id' => $article->id, 'warehouse_id' => $warehouseA->id,
            'type' => StockMovementType::Adjustment, 'quantity' => -5, 'user_id' => $this->user->id,
        ]);

        // Transfert : 30 unités de A vers B
        // Impact attendu : A = 55, B = 30. Total Article = 85.
        StockMovement::create([
            'tenants_id' => $this->tenant->id, 'article_id' => $article->id, 'warehouse_id' => $warehouseA->id,
            'target_warehouse_id' => $warehouseB->id, 'type' => StockMovementType::Transfer,
            'quantity' => 30, 'user_id' => $this->user->id,
        ]);

        // 3. Exécution de la commande
        $this->artisan('inventory:sync-totals')->assertSuccessful();

        // 4. Vérification Dépôt A (55.0)
        $qtyA = DB::table('article_warehouse')
            ->where('article_id', $article->id)
            ->where('warehouse_id', $warehouseA->id)
            ->value('quantity');
        expect((float) $qtyA)->toBe(55.0);

        // 5. Vérification Dépôt B (30.0)
        $qtyB = DB::table('article_warehouse')
            ->where('article_id', $article->id)
            ->where('warehouse_id', $warehouseB->id)
            ->value('quantity');
        expect((float) $qtyB)->toBe(30.0);

        // 6. Vérification du total dénormalisé sur l'article
        $article->refresh();
        expect((float) $article->total_stock)->toBe(85.0);
    });
});
