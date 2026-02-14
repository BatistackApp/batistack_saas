<?php

namespace App\Jobs\Pilotage;

use App\Models\Core\Tenants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GlobalKpiOrchestratorJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        Tenants::where('is_active', true)->chunk(50, function ($tenants) {
            foreach ($tenants as $tenant) {
                TakeTenantSnapshotsJob::dispatch($tenant->id);
            }
        });
        PruneOldSnapshotsJob::dispatch();
    }
}
