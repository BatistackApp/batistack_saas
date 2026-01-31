<?php

use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Services\Articles\InventoryService;

beforeEach(function () {
    $this->inventoryService = new InventoryService();
});

it('calcule correctement le nouveau CUMP après une réception', function () {
    // FIX : Créer un tenant et un dépôt pour éviter l'erreur de clé étrangère (SQLSTATE 23000)
    $tenant = Tenants::factory()->create();
    $warehouse = Warehouse::factory()->create(['tenants_id' => $tenant->id]);

    $article = Article::factory()->create([
        'tenants_id' => $tenant->id,
        'cump_ht' => 10.00,
    ]);

    // Utilisation de $warehouse->id au lieu d'un ID statique "1"
    $article->warehouses()->attach($warehouse->id, ['quantity' => 10]);

    // Action : Réception de 10 unités à 20.00 €
    // Valeur initiale (10 * 10 = 100) + Nouvelle (10 * 20 = 200) = 300 / 20 unités = 15.00 €
    $this->inventoryService->updateCump($article, 10, 20.00);

    $article->refresh();
    expect((float)$article->cump_ht)->toBe(15.00)
        ->and((float)$article->purchase_price_ht)->toBe(20.00);
});

it('ne modifie pas le CUMP si la quantité totale devient nulle ou négative', function () {
    $tenant = Tenants::factory()->create();
    $article = Article::factory()->create([
        'tenants_id' => $tenant->id,
        'cump_ht' => 10.00
    ]);

    $this->inventoryService->updateCump($article, -10, 5.00);

    $article->refresh();
    expect((float) $article->cump_ht)->toBe(10.00);
});
