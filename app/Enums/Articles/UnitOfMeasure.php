<?php

namespace App\Enums\Articles;

enum UnitOfMeasure: string
{
    case Unitaire = 'unitaire';
    case Kilogramme = 'kg';
    case Metre = 'm';
    case MetreLineaire = 'ml';
    case MetreQuarre = 'm2';
    case MetreCube = 'm3';
    case Litre = 'l';
    case Heure = 'heure';
    case Jour = 'jour';
}
