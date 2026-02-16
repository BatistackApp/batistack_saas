<?php

use App\Enums\Articles\ArticleUnit;
use App\Models\Articles\Article;
use App\Models\Articles\ArticleCategory;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe("API: Articles Controller", function () {
    beforeEach(function () {
        // Création du tenant de test et de l'utilisateur associé
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
        $this->tenant = Tenants::factory()->create();
        $this->user = User::factory()->create([
            'tenants_id' => $this->tenant->id
        ]);
        $this->user->givePermissionTo('inventory.manage');
    });

    it('liste les articles du tenant avec pagination et calculs de stocks', function () {
        // Création d'un article avec un stock bas
        $articleLow = Article::factory()->create([
            'tenants_id' => $this->tenant->id,
            'alert_stock' => 10,
            'total_stock' => 5 // Persisté via l'observer
        ]);

        // Création d'un article d'un autre tenant (ne doit pas apparaître)
        $otherTenant = Tenants::factory()->create();
        Article::factory()->create(['tenants_id' => $otherTenant->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/articles/article');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id', 'sku', 'name', 'unit', 'total_stock', 'is_low_stock'
                    ]
                ],
                'links',
            ]);

        // Vérification de l'isolation et des calculs
        $data = $response->json('data');
        expect($data)->toHaveCount(1)
            ->and($data[0]['id'])->toBe($articleLow->id)
            ->and($data[0]['is_low_stock'])->toBeTrue();
    });

    it('crée un nouvel article avec des données valides', function () {
        $category = ArticleCategory::factory()->create(['tenants_id' => $this->tenant->id]);

        $payload = [
            'tenants_id' => $this->tenant->id,
            'category_id' => $category->id,
            'sku' => 'ART-2026-X1',
            'name' => 'Câble RO2V 3G1.5',
            'unit' => ArticleUnit::Meter->value,
            'sale_price_ht' => 1.25,
            'min_stock' => 50,
            'alert_stock' => 100,
            'poids' => 0.12,
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/article', $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('articles', [
            'sku' => 'ART-2026-X1',
            'tenants_id' => $this->tenant->id
        ]);
    });

    it('échoue à la création si le SKU est déjà utilisé dans le même tenant', function () {
        Article::factory()->create([
            'tenants_id' => $this->tenant->id,
            'sku' => 'DUPLICATE-01'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/articles/article', [
                'sku' => 'DUPLICATE-01',
                'name' => 'Test',
                'unit' => ArticleUnit::Unit->value,
                'sale_price_ht' => 10,
                'min_stock' => 0,
                'alert_stock' => 0,
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    });

    it('affiche les détails d\'un article spécifique', function () {
        $article = Article::factory()->create([
            'tenants_id' => $this->tenant->id,
            'sku' => 'ART-2026-X1',
            'name' => 'Câble RO2V 3G1.5',
            'unit' => ArticleUnit::Meter->value,
            'sale_price_ht' => 1.25,
            'min_stock' => 50,
            'alert_stock' => 100,
            'poids' => 0.12,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/articles/article/{$article->id}");


        $response->assertStatus(200)
            ->assertJsonPath('sku', $article->sku);
    });

    it('interdit l\'accès à un article d\'un autre tenant', function () {
        $otherTenant = Tenants::factory()->create();
        $article = Article::factory()->create(['tenants_id' => $otherTenant->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/articles/article/{$article->id}");

        // Doit retourner 404 via le Global Scope de Tenancy
        $response->assertStatus(404);
    });
});
