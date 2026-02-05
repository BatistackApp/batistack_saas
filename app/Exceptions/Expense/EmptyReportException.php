<?php

namespace App\Exceptions\Expense;

use Exception;

class EmptyReportException extends ExpenseModuleException
{
    protected $message = "Impossible de soumettre une note de frais sans aucune ligne de dépense.";
}
