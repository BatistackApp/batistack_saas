<?php

namespace App\Jobs\HR;

use App\Models\HR\Employee;
use App\Notifications\HR\EmployeeOnboardedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProcessEmployeeOnboardingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Employee $employee) {}

    public function handle(): void
    {
        try {
            // Simulation de tâches lourdes :
            // 1. Génération du contrat PDF
            // 2. Création du compte sur le logiciel tiers
            // 3. Attribution des badges par défaut

            $this->employee->user->notify(new EmployeeOnboardedNotification($this->employee));
            Log::info("Onboarding technique finalisé pour : {$this->employee->full_name}");
        } catch (\Exception $e) {
            Log::error("Erreur onboarding {$this->employee->id}: ".$e->getMessage());
            $this->fail($e);
        }
    }
}
