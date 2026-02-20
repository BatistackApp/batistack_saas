<?php

namespace App\Observers\Core;

use App\Jobs\Core\SendTenantReactivationNotificationJob;
use App\Jobs\Core\SendTenantSuspensionNotificationJob;
use App\Models\Core\Tenants;
use App\Services\Core\OvhDomainService;
use Log;

class TenantsObserver
{
    public function __construct(
        private OvhDomainService $ovhDomainService,
    ) {}

    public function created(Tenants $tenants): void {}

    public function updating(Tenants $tenants): void
    {
        // Empêcher la modification du slug et du database
        if ($tenants->isDirty('slug') || $tenants->isDirty('database')) {
            throw new \Exception('Cannot modify slug or database after creation');
        }
    }

    public function updated(Tenants $tenant): void
    {
        if (! $tenant->isDirty('status')) {
            return;
        }

        $newStatus = $tenant->status;

        match ($newStatus) {
            'suspended' => $this->handleSuspension($tenant),
            'active' => $this->handleReactivation($tenant),
            default => null,
        };
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

    private function handleSuspension(Tenants $tenant): void
    {
        Log::warning('Tenant suspended', [
            'tenant_id' => $tenant->id,
            'reason' => 'Likely payment issue or manual suspension',
        ]);

        dispatch(new SendTenantSuspensionNotificationJob($tenant->id));
    }

    private function handleReactivation(Tenants $tenant): void
    {
        Log::info('Tenant reactivated', [
            'tenant_id' => $tenant->id,
        ]);

        dispatch(new SendTenantReactivationNotificationJob($tenant->id));
    }
}
