<?php

namespace App\Exceptions\Expense;

use Exception;

class MissingReceiptException extends ExpenseModuleException
{
    protected $message = "Un justificatif numérique est obligatoire pour ce type de dépense.";
}
