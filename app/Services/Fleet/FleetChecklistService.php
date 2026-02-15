<?php

namespace App\Services\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleCheck;
use App\Models\Fleet\VehicleChecklistTemplate;
use DB;

class FleetChecklistService
{
    /**
     * Récupère le template actif correspondant au type de véhicule.
     * Le scope HasTenant s'occupe de l'isolation SaaS au niveau du modèle.
     */
    public function getActiveTemplateForVehicle(Vehicle $vehicle): ?VehicleChecklistTemplate
    {
        return VehicleChecklistTemplate::where('vehicle_type', $vehicle->type)
            ->where('is_active', true)
            ->with(['questions' => fn($q) => $q->orderBy('sort_order')])
            ->first();
    }

    /**
     * Traite et enregistre une soumission de check-list (début ou fin de poste).
     * * @param Vehicle $vehicle Le véhicule concerné
     * @param int $userId L'identifiant de l'utilisateur (conducteur)
     * @param array $data Données de la soumission (type, odometer, results, etc.)
     * @return VehicleCheck
     */
    public function submitChecklist(Vehicle $vehicle, int $userId, array $data): VehicleCheck
    {
        return DB::transaction(function () use ($vehicle, $userId, $data) {
            // 1. Initialisation du record principal
            $check = VehicleCheck::create([
                'vehicle_id'           => $vehicle->id,
                'user_id'              => $userId,
                'vehicle_assignment_id' => $data['vehicle_assignment_id'] ?? null,
                'type'                 => $data['type'],
                'odometer_reading'     => $data['odometer_reading'] ?? $vehicle->current_odometer,
                'general_note'         => $data['general_note'] ?? null,
                'has_anomalie'         => false, // Sera mis à jour après analyse des résultats
            ]);

            $foundAnomalies = false;

            // 2. Traitement des réponses individuelles
            foreach ($data['results'] as $result) {
                $isAnomaly = $this->evaluateAnomaly($result);

                $check->results()->create([
                    'question_id'         => $result['question_id'],
                    'value'               => $result['value'],
                    'anomaly_description' => $result['anomaly_description'] ?? null,
                    'is_anomaly'          => $isAnomaly,
                    'photo_path'          => $result['photo_path'] ?? null,
                ]);

                if ($isAnomaly) {
                    $foundAnomalies = true;
                }
            }

            // 3. Mise à jour de l'état global du contrôle si anomalie détectée
            if ($foundAnomalies) {
                $check->update(['has_anomalie' => true]);
            }

            // 4. Mise à jour de l'odomètre du véhicule (si progression réelle)
            $this->updateVehicleOdometer($vehicle, $data['odometer_reading'] ?? null);

            return $check;
        });
    }

    /**
     * Logique de détermination d'une anomalie basée sur la valeur soumise.
     */
    protected function evaluateAnomaly(array $result): bool
    {
        // Une anomalie est détectée si explicitement marquée ou si la valeur est 'ko'
        if (isset($result['is_anomaly']) && $result['is_anomaly'] === true) {
            return true;
        }

        return strtolower($result['value'] ?? '') === 'ko';
    }

    /**
     * Met à jour l'odomètre du véhicule si la nouvelle valeur est cohérente.
     */
    protected function updateVehicleOdometer(Vehicle $vehicle, ?float $newReading): void
    {
        if ($newReading && $newReading > $vehicle->current_odometer) {
            $vehicle->update(['current_odometer' => $newReading]);
        }
    }
}
