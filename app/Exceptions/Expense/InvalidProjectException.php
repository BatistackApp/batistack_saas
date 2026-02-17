<?php

namespace App\Exceptions\Expense;

class InvalidProjectException extends ExpenseModuleException
{
    protected $message = "Le chantier sélectionné n'existe pas ou n'appartient pas à votre entité.";
}
