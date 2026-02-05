<?php

namespace App\Jobs\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\User;
use App\Notifications\Fleet\MaintenanceAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class CheckVehicleComplianceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Vehicle $vehicle) {}

    public function handle(): void
    {
        $needsAlert = false;
        $reason = '';

        // 1. Vérification des dates d'inspections (VGP / CT)
        $nextInspection = $this->vehicle->inspections()
            ->where('next_due_date', '<=', now()->addDays(30))
            ->first();

        if ($nextInspection) {
            $needsAlert = true;
            $reason = 'Échéance '.$nextInspection->type->value.' proche ('.$nextInspection->next_due_date->format('d/m/Y').')';
        }

        // 2. Vérification des seuils de maintenance (Exemple: vidange tous les 20 000 km)
        // Note: Cette logique pourrait être plus complexe avec un carnet d'entretien
        if ($this->vehicle->current_odometer > 0 && ($this->vehicle->current_odometer % 20000) > 19500) {
            $needsAlert = true;
            $reason = 'Maintenance préventive recommandée (Seuil odomètre)';
        }

        if ($needsAlert) {
            $recipients = User::role(['fleet_manager', 'tenant_admin'])->get();
            Notification::send($recipients, new MaintenanceAlertNotification($this->vehicle, $reason));
        }
    }
}
