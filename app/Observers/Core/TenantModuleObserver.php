<?php

namespace App\Observers\Core;

use App\Models\Core\TenantModule;
use App\Services\Core\ModuleAccessService;
use Log;

class TenantModuleObserver
{
    public function __construct(
        private ModuleAccessService $accessService,
    ) {}

    public function created(TenantModule $tenantModule): void
    {
        Log::info("Module activated for tenant", [
            'tenant_id' => $tenantModule->tenants_id,
            'module_id' => $tenantModule->module_id,
            'status' => $tenantModule->status,
        ]);

        $this->accessService->invalidateModuleCache($tenantModule->tenants_id);
    }

    public function updated(TenantModule $tenantModule): void
    {
        if ($tenantModule->isDirty('status') || $tenantModule->isDirty('ends_at')) {
            Log::info("Module status changed for tenant", [
                'tenant_id' => $tenantModule->tenants_id,
                'module_id' => $tenantModule->module_id,
                'old_status' => $tenantModule->getOriginal('status'),
                'new_status' => $tenantModule->status,
            ]);

            $this->accessService->invalidateModuleCache($tenantModule->tenants_id);
        }
    }

    public function deleted(TenantModule $tenantModule): void
    {
        Log::warning("Module deactivated for tenant", [
            'tenant_id' => $tenantModule->tenants_id,
            'module_id' => $tenantModule->module_id,
        ]);

        $this->accessService->invalidateModuleCache($tenantModule->tenants_id);
    }
}
