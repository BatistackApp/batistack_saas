<?php

namespace App\Exceptions\Payroll;

use Exception;

class MissingEmployeeDataException extends PayrollModuleException
{
    protected $message = "Données contractuelles manquantes pour l'employé (Salaire de base ou Taux horaire).";
}
