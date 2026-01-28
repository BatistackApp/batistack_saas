<?php

namespace App\Observers\Core;

use App\Models\Core\Tenants;
use App\Services\Core\OvhDomainService;
use Log;

class TenantsObserver
{
    public function __construct(
        private OvhDomainService $ovhDomainService,
    ) {}

    public function created(Tenants $tenants): void
    {
        Log::info("Tenant created: {$tenants->slug}", ['tenant_id' => $tenants->id]);
    }

    public function updating(Tenants $tenants): void
    {
        // Empêcher la modification du slug et du database
        if ($tenants->isDirty('slug') || $tenants->isDirty('database')) {
            throw new \Exception('Cannot modify slug or database after creation');
        }
    }

    public function deleting(Tenants $tenants): void
    {
        Log::warning("Tenant soft-deleted: {$tenants->slug}", ['tenant_id' => $tenants->id]);
    }

    public function restoring(Tenants $tenants): void
    {
        Log::info("Tenant restored: {$tenants->slug}", ['tenant_id' => $tenants->id]);
    }

    public function forceDeleting(Tenants $tenants): void
    {
        Log::critical("Tenant force-deleted: {$tenants->slug}", ['tenant_id' => $tenants->id]);

        $this->ovhDomainService->deleteSubdomain($tenants->slug);

        dispatch(new \App\Jobs\Core\DeleteTenantDatabaseJob($tenants->database));
    }
}
