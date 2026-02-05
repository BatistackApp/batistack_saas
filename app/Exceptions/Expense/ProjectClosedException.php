<?php

namespace App\Exceptions\Expense;

use Exception;

class ProjectClosedException extends ExpenseModuleException
{
    protected $message = "Impossible d'imputer un frais à un chantier clôturé ou suspendu.";
}
