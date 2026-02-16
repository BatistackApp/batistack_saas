<?php

namespace App\Jobs\HR;

use App\Models\HR\EmployeeSkill;
use App\Notifications\HR\SkillExpiringNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckExpiringSkillsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $thresholds = [60, 30, 7];

        foreach ($thresholds as $days) {
            $expiringSkills = EmployeeSkill::query()
                ->whereNotNull('expiry_date')
                ->whereDate('expiry_date', now()->addDays($days)->toDateString())
                ->with(['employee.manager', 'skill'])
                ->get();

            foreach ($expiringSkills as $es) {
                // On notifie le manager de l'employé s'il existe
                if ($es->employee->manager) {
                    $es->employee->manager->notify(new SkillExpiringNotification($es, $days));
                }

                // On pourrait aussi notifier un groupe de sécurité "HR_ADMIN" ici
            }
        }
    }
}
