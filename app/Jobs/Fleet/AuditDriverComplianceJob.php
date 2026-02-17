<?php

namespace App\Jobs\Fleet;

use App\Models\Fleet\VehicleAssignment;
use App\Notifications\Fleet\DriverComplianceAlertNotification;
use App\Services\Fleet\FleetComplianceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class AuditDriverComplianceJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Ce Job vérifie que les conducteurs actuellement en mission
     * possèdent toujours les habilitations requises par leurs véhicules.
     */
    public function handle(FleetComplianceService $complianceService): void
    {
        // 1. Récupérer toutes les affectations en cours
        $activeAssignments = VehicleAssignment::with(['vehicle', 'user.tier'])
            ->whereNull('ended_at')
            ->get();

        foreach ($activeAssignments as $assignment) {
            if (! $assignment->user || ! $assignment->vehicle) {
                continue;
            }

            // 2. Utiliser le service de conformité pour le diagnostic
            $check = $complianceService->checkDriverCompliance(
                $assignment->vehicle,
                $assignment->user
            );

            // 3. Si non conforme, on génère une alerte
            if (! $check['status']) {
                $this->alertManagers($assignment, $check['message']);
            }
        }
    }

    /**
     * Notifie les gestionnaires de flotte d'une anomalie de conformité.
     */
    protected function alertManagers(VehicleAssignment $assignment, string $reason): void
    {
        $tenantId = $assignment->tenants_id;

        // On récupère les admins ou managers de ce tenant
        $managers = User::role(['fleet_manager', 'tenant_admin'])
            ->where('tenants_id', $tenantId)
            ->get();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new DriverComplianceAlertNotification(
                vehicle: $assignment->vehicle,
                driver: $assignment->user,
                reason: $reason
            ));
        }
    }
}
