<?php

namespace App\Jobs\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\User;
use App\Notifications\Fleet\MaintenanceDueNotification;
use App\Services\Fleet\FleetMaintenanceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class ScanVehiclesForMaintenanceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(FleetMaintenanceService $service): void
    {
        // On cible les véhicules actifs
        Vehicle::where('is_active', true)->chunk(100, function ($vehicles) use ($service) {
            foreach ($vehicles as $vehicle) {
                $duePlans = $service->checkDueMaintenances($vehicle);

                if ($duePlans->isNotEmpty()) {
                    $recipients = User::role(['fleet.manage', 'admin'])
                        ->where('tenants_id', $vehicle->tenants_id)
                        ->get();

                    foreach ($duePlans as $plan) {
                        Notification::send($recipients, new MaintenanceDueNotification($vehicle, $plan));
                    }
                }
            }
        });
    }
}
