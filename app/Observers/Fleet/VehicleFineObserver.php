<?php

namespace App\Observers\Fleet;

use App\Jobs\Fleet\ProcessFineMatchingJob;
use App\Models\Fleet\VehicleFine;

class VehicleFineObserver
{
    public function created(VehicleFine $fine): void
    {
        ProcessFineMatchingJob::dispatch($fine);
    }
}
