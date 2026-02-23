<?php

namespace App\Enums\Payroll;

enum PayrollScaleCategory: string
{
    case Ouvrier = 'ouvrier';
    case Etam = 'etam';
    case Cadre = 'cadre';
    case All = 'all';
}
