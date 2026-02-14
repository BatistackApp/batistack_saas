<?php

namespace App\Observers\Pilotage;

use App\Models\Pilotage\KpiIndicator;

class KpiIndicatorObserver
{
    public function creating(KpiIndicator $indicator): void
    {
        if (empty($indicator->code)) {
            $indicator->code = Str::slug($indicator->name, '_');
        }
    }
}
