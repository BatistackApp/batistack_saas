<?php

use App\Enums\Commerce\InvoiceStatus;
use App\Models\Commerce\Invoices;
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
    $userA = User::factory()->create(['tenants_id' => $tenantA->id]);

    $tenantB = Tenants::factory()->create();
    $userB = User::factory()->create(['tenants_id' => $tenantB->id]);

    // On passe reference à null pour laisser l'Observer générer la séquence
    $this->actingAs($userA);
    $quoteA = Quote::factory()->create(['tenants_id' => $tenantA->id, 'reference' => null]);

    $this->actingAs($userB);
    $quoteB = Quote::factory()->create(['tenants_id' => $tenantB->id, 'reference' => null]);

    // Les deux doivent avoir le numéro 00001 pour leur tenant respectif
    expect($quoteA->reference)->toContain('00001')
        ->and($quoteB->reference)->toContain('00001');
});

it('scelle la référence de facture uniquement lors de la validation', function () {
    $tenant = Tenants::factory()->create();
    $user = User::factory()->create(['tenants_id' => $tenant->id]);
    $tier = \App\Models\Tiers\Tiers::factory()->create(['tenants_id' => $tenant->id]);
    $project = \App\Models\Projects\Project::factory()->create([
        'tenants_id' => $tenant->id,
        'customer_id' => $tier->id,
    ]);

    $invoice = Invoices::factory()->create([
        'tenants_id' => $tenant->id,
        'status' => InvoiceStatus::Draft,
        'reference' => 'TEMP-XYZ',
        'tiers_id' => $tier->id,
        'project_id' => $project->id,
    ]);

    // Force le statut en base pour éviter les effets de bord des Observers
    $invoice->updateQuietly(['status' => InvoiceStatus::Draft]);

    // Action : Validation via le contrôleur
    $response = $this->actingAs($user)
        ->postJson("/api/commerce/invoices/{$invoice->id}/validate");

    $response->assertStatus(200);

    $invoice->refresh();
    expect($invoice->status)->toBe(InvoiceStatus::Validated)
        ->and($invoice->reference)->toMatch('/SIT-\d{4}-\d{5}/');
});
