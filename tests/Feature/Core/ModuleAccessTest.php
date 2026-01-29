<?php

use App\Enums\Core\TenantModuleStatus;
use App\Models\Core\ModuleCatalog;
use App\Models\Core\TenantModule;
use App\Models\Core\Tenants;
use App\Services\Core\ModuleAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('allows access to active module', function () {
    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create(['slug' => 'banque']);

    TenantModule::factory()->create([
        'tenants_id' => $tenant->id,
        'module_id' => $module->id,
        'status' => TenantModuleStatus::Active->value,
        'starts_at' => now()->subDay(),
        'ends_at' => null,
    ]);

    $service = app(ModuleAccessService::class);

    expect($service->canAccessModule($tenant, 'banque'))->toBeTrue();
});

it('denies access to suspended module', function () {
    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create(['slug' => 'gpao']);

    TenantModule::factory()->create([
        'tenants_id' => $tenant->id,
        'module_id' => $module->id,
        'status' => TenantModuleStatus::Suspended->value,
    ]);

    $service = app(ModuleAccessService::class);

    expect($service->canAccessModule($tenant, 'gpao'))->toBeFalse();
});

it('denies access to expired module', function () {
    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create(['slug' => 'flottes']);

    TenantModule::factory()->create([
        'tenants_id' => $tenant->id,
        'module_id' => $module->id,
        'status' => TenantModuleStatus::Active->value,
        'ends_at' => now()->subDay(),
    ]);

    $service = app(ModuleAccessService::class);

    expect($service->canAccessModule($tenant, 'flottes'))->toBeFalse();
});

it('retrieves active modules for tenant', function () {
    $tenant = Tenants::factory()->create();

    $activeModule = ModuleCatalog::factory()->create(['slug' => 'banque']);
    TenantModule::factory()->create([
        'tenants_id' => $tenant->id,
        'module_id' => $activeModule->id,
        'status' => TenantModuleStatus::Active->value,
        'starts_at' => now()->subDays(),
        'ends_at' => null,
    ]);

    $inactiveModule = ModuleCatalog::factory()->create(['slug' => 'gpao']);
    TenantModule::factory()->create([
        'tenants_id' => $tenant->id,
        'module_id' => $inactiveModule->id,
        'status' => TenantModuleStatus::Suspended->value,
        'starts_at' => now()->subDay(),
    ]);

    $service = app(ModuleAccessService::class);
    $activeModules = $service->getActiveModules($tenant);

    expect($activeModules)->toHaveCount(1)
        ->toHaveKey('banque');
});
