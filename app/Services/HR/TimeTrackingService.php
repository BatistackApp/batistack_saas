<?php

namespace App\Services\HR;

use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\TimeEntry;
use DB;
use Illuminate\Support\Facades\Auth;

class TimeTrackingService
{
    /**
     * Calcule la rentabilité d'une phase de projet basée sur les heures pointées.
     */
    public function getPhaseLaborProfitability(int $projectPhaseId): array
    {
        $entries = TimeEntry::where('project_phase_id', $projectPhaseId)
            ->where('status', TimeEntryStatus::Approved)
            ->with('employee')
            ->get();

        $totalHours = $entries->sum('hours');
        $totalCost = $entries->sum(fn ($entry) => $entry->hours * $entry->employee->hourly_cost_charged);

        return [
            'total_hours' => $totalHours,
            'actual_cost_ht' => $totalCost,
            'entries_count' => $entries->count(),
        ];
    }

    /**
     * Valide un lot de pointages (Bulk Approval).
     */
    public function approveBulk(array $entryIds): int
    {
        return DB::transaction(function () use ($entryIds) {
            $count = 0;
            $entries = TimeEntry::whereIn('id', $entryIds)
                ->where('status', TimeEntryStatus::Submitted)
                ->get();

            foreach ($entries as $entry) {
                $this->approveEntry($entry);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Logique d'approbation unitaire.
     */
    public function approveEntry(TimeEntry $entry): void
    {
        if ($entry->status === TimeEntryStatus::Approved) {
            return;
        }

        $entry->update([
            'status' => TimeEntryStatus::Approved,
            'verified_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        // Mise à jour de la dénormalisation du coût sur le projet
        $this->imputeCostToProject($entry);
    }

    /**
     * Impute financièrement le coût de l'heure au projet associé.
     */
    protected function imputeCostToProject(TimeEntry $entry): void
    {
        $cost = $entry->hours * $entry->employee->hourly_cost_charged;
        $entry->project()->increment('labor_costs_actual_ht', $cost);
    }
}
