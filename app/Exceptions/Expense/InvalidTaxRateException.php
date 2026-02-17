<?php

namespace App\Exceptions\Expense;

class InvalidTaxRateException extends ExpenseModuleException
{
    protected $message = "Le taux de TVA fourni n'est pas valide ou n'est pas autorisé pour ce tenant.";
}
