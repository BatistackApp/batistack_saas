<?php

namespace App\Services\Fleet;

use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\MaintenanceType;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleMaintenance;
use App\Models\Fleet\VehicleMaintenancePlan;
use Carbon\Carbon;
use DB;
use Illuminate\Database\Eloquent\Collection;

class FleetMaintenanceService
{
    /**
     * Identifie les plans de maintenance arrivant à échéance pour un véhicule.
     */
    public function checkDueMaintenances(Vehicle $vehicle): Collection
    {
        $plans = VehicleMaintenancePlan::where('vehicle_type', $vehicle->type)
            ->where('is_active', true)
            ->get();

        return $plans->filter(function ($plan) use ($vehicle) {
            return $this->isPlanDue($vehicle, $plan);
        });
    }

    /**
     * Détermine si un plan spécifique doit être déclenché (Logique du premier terme atteint).
     */
    public function isPlanDue(Vehicle $vehicle, VehicleMaintenancePlan $plan): bool
    {
        // 1. Récupération de la dernière intervention terminée pour ce plan
        $lastMaintenance = VehicleMaintenance::where('vehicle_id', $vehicle->id)
            ->where('vehicle_maintenance_plan_id', $plan->id)
            ->where('maintenance_status', MaintenanceStatus::Completed)
            ->latest('completed_at')
            ->first();

        $baseOdometer = $lastMaintenance?->odometer_reading ?? 0;
        $baseHours = $lastMaintenance?->hours_reading ?? 0;
        $baseDate = $lastMaintenance?->completed_at ?? $vehicle->purchase_date ?? $vehicle->created_at;

        // Check Kilomètres
        if ($plan->interval_km && ($vehicle->current_odometer - $baseOdometer) >= $plan->interval_km) {
            return true;
        }

        // Check Heures moteur
        if ($plan->interval_hours && ($vehicle->current_hours - $baseHours) >= $plan->interval_hours) {
            return true;
        }

        // Check Temps (mois)
        if ($plan->interval_month && Carbon::parse($baseDate)->addMonths($plan->interval_month)->isPast()) {
            return true;
        }

        return false;
    }

    /**
     * Enregistre un incident curatif (Remontée terrain).
     */
    public function reportIncident(Vehicle $vehicle, int $userId, string $description, array $metadata = []): VehicleMaintenance
    {
        return VehicleMaintenance::create([
            'tenants_id' => $vehicle->tenants_id,
            'vehicle_id' => $vehicle->id,
            'reported_by' => $userId,
            'maintenance_type' => MaintenanceType::Curative,
            'maintenance_status' => MaintenanceStatus::Reported,
            'description' => $description,
            'reported_at' => now(),
            // On capture les compteurs au moment du signalement
            'odometer_reading' => $vehicle->current_odometer,
            'hours_reading' => $vehicle->current_hours,
        ]);
    }

    /**
     * Clôture une intervention et met à jour les métriques.
     */
    public function completeIntervention(VehicleMaintenance $maintenance, array $data): bool
    {
        return DB::transaction(function () use ($maintenance, $data) {
            $completedAt = isset($data['completed_at']) ? Carbon::parse($data['completed_at']) : now();

            // Calcul du temps d'immobilisation en heures
            $downtime = 0;
            if ($maintenance->started_at) {
                $downtime = $maintenance->started_at->diffInHours($completedAt);
            }

            $maintenance->update([
                'maintenance_status' => MaintenanceStatus::Completed,
                'completed_at' => $completedAt,
                'resolution_notes' => $data['resolution_notes'] ?? null,
                'cost_parts' => $data['cost_parts'] ?? 0,
                'cost_labor' => $data['cost_labor'] ?? 0,
                'odometer_reading' => $data['odometer_reading'] ?? $maintenance->vehicle->current_odometer,
                'hours_reading' => $data['hours_reading'] ?? $maintenance->vehicle->current_hours,
                'technician_name' => $data['technician_name'] ?? $maintenance->technician_name,
                'downtime_hours' => $downtime,
            ]);

            if (isset($data['odometer_reading']) && $data['odometer_reading'] > $maintenance->vehicle->current_odometer) {
                $maintenance->vehicle->update(['current_odometer' => $data['odometer_reading']]);
            }

            return $maintenance;
        });
    }

    /**
     * Enregistre une nouvelle demande de maintenance.
     */
    public function reportMaintenance(array $data, int $reportedById): VehicleMaintenance
    {
        // Génération automatique d'une référence interne si non fournie
        $data['reported_by'] = $reportedById;
        $data['maintenance_status'] = MaintenanceStatus::Reported;

        return VehicleMaintenance::create($data);
    }

    /**
     * Passe une maintenance en état "En cours".
     */
    public function startMaintenance(VehicleMaintenance $maintenance): bool
    {
        return $maintenance->update([
            'maintenance_status' => MaintenanceStatus::InProgress,
            'started_at' => now(),
        ]);
    }

    /**
     * Annule une maintenance.
     */
    public function cancelMaintenance(VehicleMaintenance $maintenance): bool
    {
        return $maintenance->update([
            'maintenance_status' => MaintenanceStatus::Cancelled,
        ]);
    }
}
