<?php

namespace App\Console\Commands\HR;

use App\Models\HR\Employee;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckContractExpiriesCommand extends Command
{
    protected $signature = 'hr:check-expiries';

    protected $description = 'Alerte les RH sur les contrats arrivant à échéance sous 30 jours.';

    public function handle(): void
    {
        $threshold = Carbon::now()->addDays(30);

        // On récupère les employés dont la date de fin de contrat approche
        $expiringEmployees = Employee::whereNotNull('contract_end_date')
            ->where('contract_end_date', '<=', $threshold)
            ->where('is_active', true)
            ->get();

        if ($expiringEmployees->isEmpty()) {
            $this->info('Aucune fin de contrat proche détectée.');

            return;
        }

        foreach ($expiringEmployees as $employee) {
            // Notification aux administrateurs RH
            // Notification::send($hrTeam, new ContractExpiryAlertNotification($employee));
            $this->info("Alerte générée pour le contrat de : {$employee->full_name}");
        }
    }
}
