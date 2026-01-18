<?php

use App\Enums\Tiers\TierType;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use App\Services\Tiers\TierService;
use Tests\TestCase;

beforeEach(function () {
    $this->tierService = new TierService();
});

test('can create a tier', function () {
    $tenant = Tenant::factory()->create();

    $tier = $this->tierService->createTier($tenant->id, [
        'name' => 'Acme Corporation',
        'email' => 'contact@acme.com',
        'phone' => '0123456789',
        'types' => [TierType::Client->value],
    ]);

    expect($tier)->toBeInstanceOf(Tiers::class)
        ->and($tier->name)->toBe('Acme Corporation')
        ->and($tier->tenant_id)->toBe($tenant->id)
        ->and($tier->types)->toContain(TierType::Client->value);
})->group('tiers');

it('generates unique slug', function () {
    $tenant = Tenant::factory()->create();

    $tier1 = $this->tierService->createTier($tenant->id, ['name' => 'Test Company']);
    $tier2 = $this->tierService->createTier($tenant->id, ['name' => 'Test Company']);

    expect($tier1->slug)->toBe('test-company')
        ->and($tier2->slug)->toBe('test-company-1');
})->group('tiers');

it('can update tier', function () {
    $tier = Tiers::factory()->create(['name' => 'Old Name']);

    $updated = $this->tierService->updateTier($tier, ['name' => 'New Name']);

    expect($updated->name)->toBe('New Name');
})->group('tiers');

it('can search tiers by type', function () {
    $tenant = Tenant::factory()->create();
    Tiers::factory()->create([
        'tenant_id' => $tenant->id,
        'types' => [TierType::Client->value],
    ]);
    Tiers::factory()->create([
        'tenant_id' => $tenant->id,
        'types' => [TierType::Fournisseur->value],
    ]);

    $clients = $this->tierService->getTiersByType($tenant->id, TierType::Client);

    expect($clients)->toHaveCount(1)
        ->and($clients->first()->types)->toContain(TierType::Client->value);
})->group('tiers');

it('can search tiers with filters', function () {
    $tenant = Tenant::factory()->create();
    Tiers::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Fournisseur ABC',
        'types' => [TierType::Fournisseur->value],
        'is_active' => true,
    ]);
    Tiers::factory()->create([
        'tenant_id' => $tenant->id,
        'name' => 'Client XYZ',
        'types' => [TierType::Client->value],
        'is_active' => true,
    ]);

    $results = $this->tierService->searchTiers($tenant->id, [
        'search' => 'ABC',
        'per_page' => 15,
    ]);

    expect($results->total())->toBe(1)
        ->and($results->first()->name)->toBe('Fournisseur ABC');
})->group('tiers');
