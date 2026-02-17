<?php

use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Services\Articles\InventoryService;

beforeEach(function () {
    $this->inventoryService = new InventoryService;
});

it('calcule correctement le nouveau CUMP après une réception', function () {
    $tenant = Tenants::factory()->create();
    $warehouse = Warehouse::factory()->create(['tenants_id' => $tenant->id]);

    // Article avec stock initial de 10 unités à 10.00€
    $article = Article::factory()->create([
        'tenants_id' => $tenant->id,
        'cump_ht' => 10.00,
        'total_stock' => 10,
    ]);

    $article->warehouses()->attach($warehouse->id, ['quantity' => 10]);

    // Action : Réception de 10 nouvelles unités à 20.00€
    // Calcul : (10*10 + 10*20) / 20 = 15.00€
    $this->inventoryService->updateCump($article, 10, 20.00);

    $article->refresh();
    expect((float) $article->cump_ht)->toBe(15.00);
});

it('vérifie correctement la disponibilité du stock par dépôt', function () {
    $tenant = Tenants::factory()->create();
    $warehouse = Warehouse::factory()->create(['tenants_id' => $tenant->id]);
    $article = Article::factory()->create(['tenants_id' => $tenant->id]);

    $article->warehouses()->attach($warehouse->id, ['quantity' => 50]);

    // Cas passant
    expect($this->inventoryService->hasEnoughStock($article, $warehouse, 30))->toBeTrue();

    // Cas bloquant
    expect($this->inventoryService->hasEnoughStock($article, $warehouse, 60))->toBeFalse();
});

it('ne modifie pas le CUMP si la quantité totale devient nulle ou négative', function () {
    $tenant = Tenants::factory()->create();
    $article = Article::factory()->create([
        'tenants_id' => $tenant->id,
        'cump_ht' => 10.00,
    ]);

    $this->inventoryService->updateCump($article, -10, 5.00);

    $article->refresh();
    expect((float) $article->cump_ht)->toBe(10.00);
});
