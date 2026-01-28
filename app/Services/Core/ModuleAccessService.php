<?php

namespace App\Services\Core;

use App\Models\Core\TenantModule;
use App\Models\Core\Tenants;
use Cache;

class ModuleAccessService
{
    public function canAccessModule(Tenants $tenant, string $moduleSlug): bool
    {
        return Cache::remember(
            "module:access:{$tenant->id}:{$moduleSlug}",
            ttl: 86400,
            callback: function () use ($tenant, $moduleSlug) {
                return TenantModule::where('tenants_id', $tenant->id)
                    ->whereHas('module', fn ($q) => $q->where('slug', $moduleSlug))
                    ->where(function ($q) {
                        $q->where('status', \App\Enums\Core\TenantModuleStatus::Active->value)
                            ->where('starts_at', '<=', now())
                            ->where(function ($q) {
                                $q->whereNull('ends_at')
                                    ->orWhere('ends_at', '>', now());
                            });
                    })
                    ->exists();
            }
        );
    }

    public function getActiveModules(Tenants $tenant): \Illuminate\Support\Collection
    {
        return Cache::remember(
            "modules:active:{$tenant->id}",
            ttl: 3600,
            callback: function () use ($tenant) {
                return $tenant->modules()
                    ->where('status', \App\Enums\Core\TenantModuleStatus::Active->value)
                    ->where('starts_at', '<=', now())
                    ->where(function ($q) {
                        $q->whereNull('ends_at')
                            ->orWhere('ends_at', '>', now());
                    })
                    ->with('module')
                    ->get()
                    ->mapWithKeys(fn ($tm) => [$tm->module->slug => $tm->module]);
            }
        );
    }

    public function invalidateModuleCache(int $tenantId, ?string $moduleSlug = null): void
    {
        if ($moduleSlug) {
            Cache::forget("module:access:{$tenantId}:{$moduleSlug}");
        }Cache::forget("modules:active:{$tenantId}");
    }
}
