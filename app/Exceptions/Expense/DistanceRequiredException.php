<?php

namespace App\Exceptions\Expense;

use Exception;

class DistanceRequiredException extends ExpenseModuleException
{
    protected $message = "La distance est obligatoire pour les catégories de type Indemnités Kilométriques.";
}
