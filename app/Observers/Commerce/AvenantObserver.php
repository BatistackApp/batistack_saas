<?php

namespace App\Observers\Commerce;

use App\Models\Commerce\Avenant;

class AvenantObserver
{
    public function creating(Avenant $avenant): void
    {
        if (! $avenant->number) {
            GenerateAvenantNumberJob::dispatch($avenant);
        }
    }

    public function created(Avenant $avenant): void
    {
        ComputeAvenantAmountsJob::dispatch($avenant);
    }

    public function updated(Avenant $avenant): void
    {
        if ($avenant->isDirty(['lignes'])) {
            ComputeAvenantAmountsJob::dispatch($avenant);
        }
    }
}
