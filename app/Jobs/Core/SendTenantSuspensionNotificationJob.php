<?php

namespace App\Jobs\Core;

use App\Models\Core\Tenants;
use App\Notifications\Core\TenantSuspensionNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SendTenantSuspensionNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 60;
    public int $tries = 3;

    public function __construct(
        private int $tenantId,
    )
    {
    }

    public function handle(): void
    {
        try {
            $tenant = Tenants::findOrFail($this->tenantId);

            Log::info("Sending suspension notification", [
                'tenant_id' => $this->tenantId,
            ]);

            $tenant->notify(new TenantSuspensionNotification($tenant));
        } catch (\Exception $e) {
            Log::error("Failed to send suspension notification", [
                'tenant_id' => $this->tenantId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
