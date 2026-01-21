<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\Avoir;

class AvoirObserver
{
    public function creating(Avoir $avoir): void
    {
        if (! $avoir->number) {
            GenerateAvoirNumberJob::dispatch($avoir);
        }
    }

    public function created(Avoir $avoir): void
    {
        ComputeAvoirAmountsJob::dispatch($avoir);
    }

    public function updated(Avoir $avoir): void
    {
        if ($avoir->isDirty(['lignes'])) {
            ComputeAvoirAmountsJob::dispatch($avoir);
        }
    }
}
