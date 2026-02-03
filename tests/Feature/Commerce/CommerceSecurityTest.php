<?php

use App\Models\Commerce\Quote;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('interdit à un utilisateur de voir un devis d\'un autre tenant', function () {
    $tenantA = Tenants::factory()->create();
    $userA = User::factory()->create(['tenants_id' => $tenantA->id]);

    $tenantB = Tenants::factory()->create();
    $quoteB = Quote::factory()->create(['tenants_id' => $tenantB->id]);

    $response = $this->actingAs($userA)
        ->getJson("/api/commerce/quote/{$quoteB->id}");

    // Doit renvoyer 404 (non trouvé dans le scope de l'utilisateur) ou 403
    expect($response->status())->toBeIn([404, 403]);
});

it('génère des références chronologiques distinctes par tenant', function () {
    $tenantA = Tenants::factory()->create();
    $tenantB = Tenants::factory()->create();

    $quoteA = Quote::factory()->create(['tenants_id' => $tenantA->id]);
    $quoteB = Quote::factory()->create(['tenants_id' => $tenantB->id]);

    // Les deux doivent avoir le numéro 00001 pour leur tenant respectif
    expect($quoteA->reference)->toContain('00001')
        ->and($quoteB->reference)->toContain('00001');
});
