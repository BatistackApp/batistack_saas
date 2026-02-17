<?php

namespace App\Services\HR;

use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\TimeEntry;
use App\Models\HR\TimeEntryLog;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class TimeTrackingService
{
    /**
     * Calcule la rentabilité d'une phase de projet basée sur les heures pointées.
     */
    public function getPhaseLaborProfitability(int $projectPhaseId): array
    {
        $entries = TimeEntry::where('project_phase_id', $projectPhaseId)
            ->where('status', TimeEntryStatus::Approved)
            ->get();

        $totalHours = $entries->sum('hours');
        $totalCost = $entries->sum('valuation_amount');

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
            $entries = TimeEntry::whereIn('id', $entryIds)
                ->where('status', TimeEntryStatus::Verified)
                ->get();

            foreach ($entries as $entry) {
                $this->approveEntry($entry);
            }

            return $entries->count();
        });
    }

    /**
     * Soumet un pointage pour validation.
     */
    public function submitEntry(TimeEntry $entry): void
    {
        if ($entry->status !== TimeEntryStatus::Draft && $entry->status !== TimeEntryStatus::Rejected) {
            throw ValidationException::withMessages(['status' => 'Seuls les brouillons ou les pointages rejetés peuvent être soumis.']);
        }

        $this->transition($entry, TimeEntryStatus::Submitted, "Soumission par l'utilisateur.");
    }

    /**
     * Vérification de Niveau 1 (Chef de Chantier).
     */
    public function verifyEntry(TimeEntry $entry, ?string $comment = null): void
    {
        if ($entry->status !== TimeEntryStatus::Submitted) {
            throw ValidationException::withMessages([
                'status' => "Seuls les pointages soumis peuvent être vérifiés.",
            ]);
        }

        $entry->verified_at = now();
        $entry->verified_by = Auth::id();

        $this->transition($entry, TimeEntryStatus::Verified, $comment ?? 'Vérification terrain effectuée.');
    }

    /**
     * Logique d'approbation unitaire.
     */
    public function approveEntry(TimeEntry $entry): void
    {
        if ($entry->status !== TimeEntryStatus::Verified) {
            throw ValidationException::withMessages([
                'status' => "Le pointage doit être vérifié par un manager (N1) avant l'approbation finale."
            ]);
        }

        $entry->approved_at = now();
        $entry->approved_by = Auth::id();

        $this->transition($entry, TimeEntryStatus::Approved, $comment ?? 'Approbation finale et clôture.');

        // Action financière associée
        $this->imputeCostToProject($entry);
    }

    /**
     * Rejeter un pointage (N1 ou N2).
     */
    public function rejectEntry(TimeEntry $entry, string $reason): void
    {
        if (in_array($entry->status, [TimeEntryStatus::Draft, TimeEntryStatus::Approved])) {
            throw ValidationException::withMessages(['status' => 'Impossible de rejeter un pointage déjà approuvé ou encore en brouillon.']);
        }

        $entry->rejection_note = $reason;

        $this->transition($entry, TimeEntryStatus::Rejected, 'Rejet : '.$reason);
    }

    /**
     * Gère la transition technique et le log.
     */
    protected function transition(TimeEntry $entry, TimeEntryStatus $to, ?string $comment = null): void
    {
        $from = $entry->status;

        DB::transaction(function () use ($entry, $from, $to, $comment) {
            $entry->status = $to;
            $entry->save();

            TimeEntryLog::create([
                'time_entry_id' => $entry->id,
                'user_id' => Auth::id() ?? $entry->employee->user_id,
                'from_status' => $from->value,
                'to_status' => $to->value,
                'comment' => $comment,
            ]);
        });
    }

    /**
     * Impute financièrement le coût de l'heure au projet associé.
     */
    protected function imputeCostToProject(TimeEntry $entry): void
    {
        // On récupère le coût horaire chargé de l'employé au moment de la validation
        $hourlyRate = $entry->employee->hourly_cost_charged ?? 0;
        $totalCost = $entry->hours * $hourlyRate;

        // Ici, on pourrait mettre à jour une table 'project_costs' ou envoyer vers la GPAO
        // Pour l'instant, nous stockons l'info de coût dénormalisée si besoin
        $entry->updateQuietly(['valuation_amount' => $totalCost]);
        $entry->project()->increment('actual_labor_cost', $totalCost);
    }
}
