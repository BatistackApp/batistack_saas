<?php

use App\Enums\Core\TenantModuleStatus;
use App\Enums\Core\TenantStatus;
use App\Models\Core\ModuleCatalog;
use App\Models\Core\TenantModule;
use App\Models\Core\Tenants;
use App\Services\Core\TenantProvisioningService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('provisions a tenant with all core modules', function () {
    // Arrange : Créer les modules core
    $coreModules = ModuleCatalog::factory()
        ->count(8)
        ->state(['is_core' => true])
        ->create();

    $service = app(TenantProvisioningService::class);

    // Act : Provisionner un tenant
    $tenant = $service->provision([
        'name' => 'Test Tenant',
        'slug' => 'test-tenant',
        'custom_domain' => null,
        'settings' => [],
        'email' => 'test@test.com',
    ]);

    // Assert : Vérifier la création du tenant
    expect($tenant)
        ->id->toBeGreaterThan(0)
        ->name->toBe('Test Tenant')
        ->slug->toBe('test-tenant')
        ->status->toBe(TenantStatus::Active)
        ->and($tenant->modules)->toHaveCount(8);

    // Vérifier que tous les modules core sont activés

    $tenant->modules->each(function (TenantModule $module) {
        expect($module->status)->toBe(TenantModuleStatus::Active);
    });
});

it('fails to provision a tenant with duplicate slug', function () {
    Tenants::factory()->create(['slug' => 'existing-tenant']);

    $service = app(TenantProvisioningService::class);

    expect(fn () => $service->provision([
        'name' => 'Duplicate Tenant',
        'slug' => 'existing-tenant',
    ]))->toThrow(\Exception::class);
});

it('creates tenant database schema', function () {
    $service = app(TenantProvisioningService::class);

    $tenant = $service->provision([
        'name' => 'Database Tenant',
        'slug' => 'db-tenant',
        'settings' => [],
        'email' => 'test@test.com',
    ]);

    // Vérifier que le tenant a été créé avec le nom de base de données
    expect($tenant)
        ->id->toBeGreaterThan(0)
        ->database->toBe('tenant_db-tenant');
});
