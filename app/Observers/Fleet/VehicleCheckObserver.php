<?php

namespace App\Observers\Fleet;

use App\Jobs\Fleet\ProcessChecklistAnomaliesJob;
use App\Models\Fleet\VehicleCheck;

class VehicleCheckObserver
{
    public function created(VehicleCheck $check): void
    {
        if ($check->has_anomalie) {
            ProcessChecklistAnomaliesJob::dispatch($check);
        }
    }
}
