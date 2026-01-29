<?php

namespace App\Services\Core;

use App\Enums\Core\TenantModuleStatus;
use App\Models\Core\TenantModule;
use App\Models\Core\Tenants;
use DB;
use Log;

class TenantModuleManager
{
    public function __construct(
        private ModuleAccessService $accessService,
    ) {}

    public function activateModule(Tenants $tenant, int $moduleId, ?array $config = null): TenantModule
    {
        return DB::transaction(function () use ($tenant, $moduleId, $config) {
            // ✅ Vérifier si le module est déjà actif
            $existingModule = $tenant->modules()
                ->where('module_id', $moduleId)
                ->where('status', TenantModuleStatus::Active)
                ->first();

            if ($existingModule) {
                Log::info('Module already active, skipping activation', [
                    'tenant_id' => $tenant->id,
                    'module_id' => $moduleId,
                ]);

                return $existingModule;
            }

            $tenantModule = TenantModule::updateOrCreate(
                [
                    'tenants_id' => $tenant->id,
                    'module_id' => $moduleId,
                ],
                [
                    'status' => \App\Enums\Core\TenantModuleStatus::Active->value,
                    'starts_at' => now(),
                    'ends_at' => null,
                    'config' => $config,
                ]
            );

            $this->accessService->invalidateModuleCache($tenant->id);

            return $tenantModule;
        });
    }

    public function suspendModule(Tenants $tenant, int $moduleId, ?string $reason = null): TenantModule
    {
        $tenantModule = $tenant->modules()
            ->where('module_id', $moduleId)
            ->firstOrFail();

        $tenantModule->update([
            'status' => \App\Enums\Core\TenantModuleStatus::Suspended->value,
        ]);

        $this->accessService->invalidateModuleCache($tenant->id);

        return $tenantModule;
    }

    public function expireModule(Tenants $tenant, int $moduleId): TenantModule
    {
        $tenantModule = $tenant->modules()
            ->where('module_id', $moduleId)
            ->firstOrFail();

        $tenantModule->update([
            'status' => \App\Enums\Core\TenantModuleStatus::Expired->value,
            'ends_at' => now(),
        ]);

        $this->accessService->invalidateModuleCache($tenant->id);

        return $tenantModule;
    }
}
