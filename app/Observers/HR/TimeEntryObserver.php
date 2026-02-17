<?php

namespace App\Observers\HR;

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\AbsenceRequest;
use App\Models\HR\TimeEntry;
use App\Notifications\HR\TimeEntryApprovedNotification;
use App\Notifications\HR\TimeEntryStatusChangedNotification;
use App\Notifications\HR\TimeEntrySubmittedNotification;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;

class TimeEntryObserver
{
    public function creating(TimeEntry $timeEntry): void
    {
        $this->checkAbsenceConflict($timeEntry);
    }

    public function created(TimeEntry $timeEntry): void
    {
        if ($timeEntry->status === TimeEntryStatus::Submitted) {
            $this->notifyManager($timeEntry);
        }
    }

    public function updating(TimeEntry $timeEntry): void
    {
        $this->checkAbsenceConflict($timeEntry);
    }

    public function updated(TimeEntry $timeEntry): void
    {
        if ($timeEntry->wasChanged('status')) {
            $status = $timeEntry->status;

            // Cas 1 : Pointage Rejeté -> On prévient l'employé immédiatement
            if ($status === TimeEntryStatus::Rejected) {
                $timeEntry->employee?->user?->notify(new TimeEntryStatusChangedNotification($timeEntry));
            }

            // Cas 2 : Pointage Soumis -> On prévient le manager (Niveau 1)
            if ($status === TimeEntryStatus::Submitted) {
                $this->notifyManager($timeEntry);
            }

            // Cas 3 : Pointage Approuvé (Final) -> On prévient l'employé
            if ($status === TimeEntryStatus::Approved) {
                $timeEntry->employee?->user?->notify(new TimeEntryStatusChangedNotification($timeEntry));
            }
        }
    }

    /**
     * Sécurité d'intégrité : On interdit la suppression d'un pointage validé.
     * En Laravel, retourner 'false' dans l'événement deleting annule l'opération.
     */
    public function deleting(TimeEntry $timeEntry): bool
    {
        if ($timeEntry->status === TimeEntryStatus::Approved || $timeEntry->status === TimeEntryStatus::Verified) {
            return false;
        }

        return true;
    }

    /**
     * Logique de notification du manager.
     */
    protected function notifyManager(TimeEntry $timeEntry): void
    {
        if (!$timeEntry->relationLoaded('employee')) {
            $timeEntry->load('employee.manager');
        }

        $manager = $timeEntry->employee?->manager;
        if ($manager) {
            $manager->notify(new TimeEntryStatusChangedNotification($timeEntry));
        }
    }

    protected function checkAbsenceConflict(TimeEntry $timeEntry): void
    {
        $exists = AbsenceRequest::where('employee_id', $timeEntry->employee_id)
            ->where('status', AbsenceRequestStatus::Approved)
            ->whereDate('starts_at', '<=', $timeEntry->date)
            ->whereDate('ends_at', '>=', $timeEntry->date)
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'date' => "Un congé validé existe déjà pour cette date ($timeEntry->date). La saisie d'heures de production est impossible.",
            ]);
        }
    }
}
