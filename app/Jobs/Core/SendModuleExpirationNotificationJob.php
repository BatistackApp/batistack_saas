<?php

namespace App\Jobs\Core;

use App\Models\Core\ModuleCatalog;
use App\Models\Core\Tenants;
use App\Notifications\Core\ModuleExpirationNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendModuleExpirationNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60; // 1 minute

    public int $tries = 3;

    public function __construct(
        public int $tenantId,
        public int $moduleId,
        private string $type = 'expired', // 'warning' ou 'expired'
    ) {}

    public function handle(): void
    {
        try {
            $tenant = Tenants::findOrFail($this->tenantId);
            $module = ModuleCatalog::findOrFail($this->moduleId);

            Log::info('Sending module expiration notification', [
                'tenant_id' => $this->tenantId,
                'module_id' => $this->moduleId,
                'type' => $this->type,
            ]);

            // Envoyer la notification aux administrateurs du tenant
            $tenant->notify(new ModuleExpirationNotification($tenant, $module, $this->type));

        } catch (\Exception $e) {
            Log::error('Failed to send module expiration notification', [
                'tenant_id' => $this->tenantId,
                'module_id' => $this->moduleId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
