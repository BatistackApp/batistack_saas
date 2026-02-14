<?php

namespace App\Jobs\Fleet;

use App\Models\Fleet\VehicleAssignment;
use App\Services\Fleet\FleetImputationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Job de régularisation analytique mensuelle.
 * Pour les affectations de longue durée (ex: grue sur 6 mois),
 * impute les coûts chaque mois sans attendre la fin de l'affectation.
 */
class MonthlyFleetImputationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FleetImputationService $imputationService): void
    {
        // On récupère les affectations en cours
        VehicleAssignment::whereNull('ended_at')
            ->whereNotNull('project_id')
            ->chunk(100, function ($assignments) use ($imputationService) {
                foreach ($assignments as $assignment) {
                    $imputationService->imputeCostsToProject($assignment);
                }
            });
    }
}
