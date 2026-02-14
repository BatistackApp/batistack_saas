<?php

namespace App\Enums\Pilotage;

enum KpiUnit: string
{
    case CURRENCY = 'currency';     // €, $
    case PERCENTAGE = 'percentage'; // %
    case COUNT = 'count';           // Unité, Nombre
    case DURATION = 'duration';     // Heures, Jours
}
