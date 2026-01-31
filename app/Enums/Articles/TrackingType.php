<?php

namespace App\Enums\Articles;

enum TrackingType: string
{
    case Quantity = 'qty';      // Suivi classique par masse/quantité
    case SerialNumber = 'sn';   // Suivi unitaire par numéro de série
}
