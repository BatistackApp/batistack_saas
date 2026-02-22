<?php

namespace App\Jobs\Intervention;

use App\Enums\Intervention\InterventionStatus;
use App\Models\Intervention\Intervention;
use App\Notifications\Intervention\TechnicianScheduleReminderNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class ReminderPlannedInterventionJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $tomorrow = now()->addDay()->toDateString();

        // On récupère les interventions planifiées pour demain avec leurs techniciens
        $interventions = Intervention::where('status', InterventionStatus::Planned)
            ->whereDate('planned_at', $tomorrow)
            ->with('technicians')
            ->get();

        foreach ($interventions as $intervention) {
            if ($intervention->technicians->isNotEmpty()) {
                Notification::send(
                    $intervention->technicians,
                    new TechnicianScheduleReminderNotification($intervention)
                );
            }
        }
    }
}
