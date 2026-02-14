<?php

namespace App\Services\Pilotage;

use App\Models\Pilotage\KpiIndicator;

interface KpiFormulaInterface
{
    public function calculate(KpiIndicator $indicator, $context = null): string;
}
