<?php

namespace App\Observers\HR;

use App\Jobs\HR\ProcessEmployeeOnboardingJob;
use App\Models\HR\Employee;

class EmployeeObserver
{
    public function created(Employee $employee): void
    {
        // On déclenche les procédures d'accueil (envoi email, création accès)
        dispatch(new ProcessEmployeeOnboardingJob($employee));
    }
}
