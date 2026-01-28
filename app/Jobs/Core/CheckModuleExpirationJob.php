<?php

namespace App\Jobs\Core;

use App\Models\Core\TenantModule;
use App\Services\Core\ModuleAccessService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class CheckModuleExpirationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes

    public function __construct(
        private ModuleAccessService $accessService
    ) {}

    public function handle(): void
    {
        try {
            Log::info("Checking module expirations...");

            // Récupérer les modules expirés (ends_at <= now())
            $expiredModules = TenantModule::where('status', '!=', \App\Enums\Core\TenantModuleStatus::Expired->value)
                ->where('ends_at', '<=', now())
                ->get();

            foreach ($expiredModules as $tenantModule) {
                $tenantModule->update([
                    'status' => \App\Enums\Core\TenantModuleStatus::Expired->value,
                ]);

                Log::info("Module marked as expired", [
                    'tenant_id' => $tenantModule->tenant_id,
                    'module_id' => $tenantModule->module_id,
                ]);

                // Invalider le cache
                $this->accessService->invalidateModuleCache($tenantModule->tenant_id);

                // Envoyer notification
                dispatch(new SendModuleExpirationNotificationJob($tenantModule->tenant_id, $tenantModule->module_id));
            }

            Log::info("Module expiration check completed. Modules expired: {$expiredModules->count()}");
        } catch (\Exception $e) {
            Log::error("Module expiration check failed", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
