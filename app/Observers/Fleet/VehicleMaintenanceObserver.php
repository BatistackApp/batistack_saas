<?php

namespace App\Observers\Fleet;

use App\Enums\Fleet\MaintenanceStatus;
use App\Models\Fleet\VehicleMaintenance;
use App\Models\User;
use App\Notifications\Fleet\MaintenanceAlertNotification;
use Illuminate\Support\Facades\Notification;

class VehicleMaintenanceObserver
{
    public function creating(VehicleMaintenance $maintenance): void
    {
        $prefix = 'MAINT-' . now()->format('Y');
        $count = VehicleMaintenance::where('internal_reference', 'like', "{$prefix}%")->count();
        $maintenance->internal_reference = sprintf("%s-%04d", $prefix, $count + 1);
    }

    public function created(VehicleMaintenance $maintenance): void
    {
        if ($maintenance->maintenance_status === MaintenanceStatus::Reported) {
            $recipients = User::role(['fleet.manage', 'admin'])
                ->where('tenants_id', $maintenance->tenants_id)
                ->get();

            Notification::send($recipients, new MaintenanceAlertNotification(
                $maintenance->vehicle,
                "Incident signalé : " . $maintenance->description
            ));
        }
    }

    public function updated(VehicleMaintenance $maintenance): void
    {
        if ($maintenance->wasChanged('maintenance_status') && $maintenance->maintenance_status === MaintenanceStatus::Completed) {
            $vehicle = $maintenance->vehicle;
            $updates = [];

            // Si les compteurs saisis lors de la maintenance sont supérieurs aux compteurs actuels, on synchronise
            if ($maintenance->odometer_reading > $vehicle->current_odometer) {
                $updates['current_odometer'] = $maintenance->odometer_reading;
            }

            if ($maintenance->hours_reading > $vehicle->current_hours) {
                $updates['current_hours'] = $maintenance->hours_reading;
            }

            if (!empty($updates)) {
                $vehicle->update($updates);
            }
        }
    }
}
