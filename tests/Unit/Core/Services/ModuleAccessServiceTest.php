<?php

use App\Enums\Core\TenantModuleStatus;
use App\Models\Core\ModuleCatalog;
use App\Models\Core\TenantModule;
use App\Models\Core\Tenants;
use App\Services\Core\ModuleAccessService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('caches module access results', function () {
    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create(['slug' => 'test-module']);

    TenantModule::factory()->create([
        'tenants_id' => $tenant->id,
        'module_id' => $module->id,
        'status' => TenantModuleStatus::Active->value,
    ]);

    $service = app(ModuleAccessService::class);

    // Premier appel
    $result = $service->canAccessModule($tenant, 'test-module');
    expect($result)->toBeTrue();

    // Vérifier que le cache est en place
    $cached = \Illuminate\Support\Facades\Cache::get(
        "module:access:{$tenant->id}:test-module"
    );
    expect($cached)->toBeTrue();
});

it('invalidates module cache correctly', function () {
    $tenant = Tenants::factory()->create();

    $service = app(ModuleAccessService::class);
    $service->invalidateModuleCache($tenant->id, 'specific-module');

    // Vérifier que le cache est supprimé
    $cached = \Illuminate\Support\Facades\Cache::get(
        "module:access:{$tenant->id}:specific-module"
    );
    expect($cached)->toBeNull();
});
