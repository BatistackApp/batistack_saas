<?php

namespace App\Observers\HR;

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\AbsenceRequest;
use App\Models\HR\TimeEntry;
use App\Notifications\HR\AbsenceRequestNotification;
use App\Traits\PayrollLockObserverTrait;
use Storage;

class AbsenceRequestObserver
{
    use PayrollLockObserverTrait;

    public function created(AbsenceRequest $request): void
    {
        $manager = $request->employee?->manager;
        if ($manager) {
            $manager->notify(new AbsenceRequestNotification($request, 'submitted'));
        }
    }

    public function saving(AbsenceRequest $request): void
    {
        // On vérifie le verrouillage pour la date de début et de fin
        $this->checkPayrollLock($request->tenants_id, $request->starts_at);
        $this->checkPayrollLock($request->tenants_id, $request->ends_at);
    }

    /**
     * Gérer les conséquences d'un changement de statut.
     */
    public function updated(AbsenceRequest $request): void
    {
        // 1. Notification de l'employé sur la décision (Approuvé ou Refusé)
        if ($request->wasChanged('status')) {
            $request->employee?->user?->notify(new AbsenceRequestNotification($request, 'status_changed'));

            if ($request->status === AbsenceRequestStatus::Approved) {
                $this->cleanupConflictingTimeEntries($request);
            }
        }
    }

    /**
     * Automatisation : Nettoyage physique du stockage.
     */
    public function deleting(AbsenceRequest $request): void
    {
        $this->checkPayrollLock($request->tenants_id, $request->starts_at);

        if ($request->justification_path) {
            Storage::disk('public')->delete($request->justification_path);
        }
    }

    /**
     * Supprime ou passe en "Refusé" les pointages de chantier sur la période validée.
     */
    protected function cleanupConflictingTimeEntries(AbsenceRequest $request): void
    {
        TimeEntry::where('employee_id', $request->employee_id)
            ->whereBetween('date', [$request->starts_at->format('Y-m-d'), $request->ends_at->format('Y-m-d')])
            ->whereIn('status', [TimeEntryStatus::Draft, TimeEntryStatus::Submitted])
            ->delete();
    }
}
