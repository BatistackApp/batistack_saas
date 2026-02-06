<?php

namespace App\Enums\Intervention;

enum BillingType: string
{
    case Regie = 'regie';       // Facturation au réel (Temps + Matériaux)
    case Forfait = 'forfait';   // Facturation à prix fixe défini
}
