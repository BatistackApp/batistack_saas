<?php

namespace App\Enums\Pilotage;

enum KpiCategory: string
{
    case FINANCIAL = 'financial';     // Rentabilité, CA, Trésorerie
    case OPERATIONAL = 'operational'; // Avancement chantier, Stocks
    case HUMAN_RESOURCES = 'hr';      // Heures, Absentéisme, Sécurité
    case EQUIPMENT = 'equipment';     // Utilisation flotte, Maintenance
}
