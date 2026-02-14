<?php

namespace App\Jobs\Pilotage;

use App\Models\Pilotage\KpiSnapshot;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class PruneOldSnapshotsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // On conserve 2 ans d'historique de KPI
        $deleted = KpiSnapshot::where('measured_at', '<', now()->subMonths(24))
            ->delete();

        if ($deleted > 0) {
            Log::info("Nettoyage KPI : {$deleted} snapshots anciens supprimés.");
        }
    }
}
