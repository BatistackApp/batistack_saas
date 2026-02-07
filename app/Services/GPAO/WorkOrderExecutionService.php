<?php

namespace App\Services\GPAO;

use App\Enums\GPAO\OperationStatus;
use App\Enums\GPAO\WorkOrderStatus;
use App\Models\GPAO\WorkOrderOperation;

/**
 * Service d'exécution des opérations en atelier.
 */
class WorkOrderExecutionService
{
    /**
     * Démarre une opération spécifique.
     */
    public function startOperation(WorkOrderOperation $operation): void
    {
        // Vérification de la séquence : l'opération précédente doit être finie
        $previousIncomplete = WorkOrderOperation::where('work_order_id', $operation->work_order_id)
            ->where('sequence', '<', $operation->sequence)
            ->where('status', '!=', OperationStatus::Finished)
            ->exists();

        if ($previousIncomplete) {
            throw new \Exception("L'opération précédente n'est pas encore terminée.");
        }

        $operation->update(['status' => OperationStatus::Running]);

        // Si c'est la première opération, on passe l'OF en "In Progress"
        if ($operation->workOrder->status === WorkOrderStatus::Planned) {
            $operation->workOrder->update([
                'status' => WorkOrderStatus::InProgress,
                'actual_start_at' => now()
            ]);
        }
    }

    /**
     * Termine une opération et enregistre le temps réel.
     */
    public function completeOperation(WorkOrderOperation $operation, float $actualMinutes): void
    {
        $operation->update([
            'status' => OperationStatus::Finished,
            'time_actual_minutes' => $actualMinutes
        ]);
    }
}
