<?php

use App\Enums\Core\TenantModuleStatus;
use App\Jobs\Core\CheckModuleExpirationJob;
use App\Jobs\Core\SendModuleExpirationNotificationJob;
use App\Models\Core\ModuleCatalog;
use App\Models\Core\TenantModule;
use App\Models\Core\Tenants;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;

uses(RefreshDatabase::class);

it('marks expired modules as expired', function () {
    Queue::fake();

    $tenant = Tenants::factory()->create();
    $module = ModuleCatalog::factory()->create();

    // Module qui expire aujourd'hui
    $expiredModule = TenantModule::factory()->create([
        'tenants_id' => $tenant->id,
        'module_id' => $module->id,
        'status' => TenantModuleStatus::Active->value,
        'ends_at' => now()->subDay(),
    ]);

    // Module encore valide
    $activeModule = TenantModule::factory()->create([
        'tenants_id' => $tenant->id,
        'module_id' => ModuleCatalog::factory()->create()->id,
        'status' => TenantModuleStatus::Active->value,
        'ends_at' => now()->addMonth(),
    ]);

    // Exécuter le job
    $job = new CheckModuleExpirationJob(app(\App\Services\Core\ModuleAccessService::class));
    $job->handle();

    // Vérifier que le module expiré est marqué
    expect($expiredModule->fresh()->status)
        ->toBe(TenantModuleStatus::Expired)
        ->and($activeModule->fresh()->status)
        ->toBe(TenantModuleStatus::Active);

    // Vérifier que le module actif reste actif

    // Vérifier que la notification a été envoyée
    Queue::assertPushed(SendModuleExpirationNotificationJob::class, function (SendModuleExpirationNotificationJob $job) use ($tenant, $module) {
        return $job->tenantId === $tenant->id && $job->moduleId === $module->id;
    });
});
