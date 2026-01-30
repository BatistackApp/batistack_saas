<?php

namespace App\Enums\Tiers;

enum TierDocumentType: string
{
    case URSSAF = 'ATTESTATON_VIGILANCE_URSSAF';
    case DECENNALE = 'ASSURANCE_RC_DECENNALE';
    case RC_PRO = 'ASSURANCE_RC_PRO';
    case KBIS = 'EXTRAIT_KBIS';
    case FOREIGN_WORKERS = 'LISTE_TRAVAILLEURS_ETRANGERS';
    case BTP_CARD = 'CARTE_BTP';
    case DC4 = 'FORMULAIRE_DC4';
}
