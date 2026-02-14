<?php

namespace App\Observers\Fleet;

use App\Models\Fleet\VehicleAssignment;
use App\Services\Fleet\FleetImputationService;

class VehicleAssignmentObserver
{
    public function __construct(
        protected FleetImputationService $imputationService
    ) {}
    public function updated(VehicleAssignment $assignment): void
    {
        if ($assignment->wasChanged('ended_at') && !empty($assignment->ended_at)) {
            $this->imputationService->imputeCostsToProject($assignment);
        }
    }

    public function deleting(VehicleAssignment $assignment): void
    {
        if (empty($assignment->ended_at)) {
            $assignment->ended_at = now();
            $this->imputationService->imputeCostsToProject($assignment);
        }
    }
}
