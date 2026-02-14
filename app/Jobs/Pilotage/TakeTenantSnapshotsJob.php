<?php

namespace App\Jobs\Pilotage;

use App\Services\Pilotage\SnapshotOrchestrator;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class TakeTenantSnapshotsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public int $tenantId) {}

    public function handle(SnapshotOrchestrator $orchestrator): void
    {
        try {
            $orchestrator->takeGlobalSnapshots($this->tenantId);
        } catch (\Exception $e) {
            Log::error("Erreur Snapshot Tenant #{$this->tenantId} : " . $e->getMessage());
        }
    }
}
