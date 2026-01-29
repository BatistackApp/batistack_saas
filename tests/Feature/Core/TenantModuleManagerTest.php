<?php

use App\Enums\Core\TenantModuleStatus;
use App\Models\Core\ModuleCatalog;
use App\Models\Core\Tenants;
use App\Services\Core\TenantModuleManager;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('activates a module for a tenant', function () {
    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create();

    $manager = app(TenantModuleManager::class);
    $tenantModule = $manager->activateModule($tenant, $module->id);

    expect($tenantModule)
        ->tenants_id->toBe($tenant->id)
        ->module_id->toBe($module->id)
        ->status->toBe(TenantModuleStatus::Active);
});

it('suspends a module for a tenant', function () {
    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create();

    $manager = app(TenantModuleManager::class);
    $manager->activateModule($tenant, $module->id);

    $suspended = $manager->suspendModule($tenant, $module->id);

    expect($suspended->status)->toBe(TenantModuleStatus::Suspended);
});

it('expires a module for a tenant', function () {
    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create();

    $manager = app(TenantModuleManager::class);
    $manager->activateModule($tenant, $module->id);

    $expired = $manager->expireModule($tenant, $module->id);

    expect($expired)
        ->status->toBe(TenantModuleStatus::Expired)
        ->ends_at->not->toBeNull();
});

it('invalidates cache when module status changes', function () {
    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create();

    $manager = app(TenantModuleManager::class);
    $manager->activateModule($tenant, $module->id);

    // Le cache doit être invalidé
    $this->assertFalse(\Illuminate\Support\Facades\Cache::has(
        "modules:active:{$tenant->id}"
    ));
});
