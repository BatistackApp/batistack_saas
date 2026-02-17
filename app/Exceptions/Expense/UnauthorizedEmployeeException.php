<?php

namespace App\Exceptions\Expense;

use Illuminate\Http\Response;

class UnauthorizedEmployeeException extends ExpenseModuleException
{
    protected $code = Response::HTTP_FORBIDDEN;

    protected $message = "L'utilisateur n'est pas enregistré comme employé actif ou n'a pas de contrat valide à cette date.";
}
