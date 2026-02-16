<?php

namespace App\Observers\HR;

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\AbsenceRequest;
use App\Models\HR\TimeEntry;
use App\Notifications\HR\TimeEntryApprovedNotification;
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
            // On récupère le manager direct de l'employé
            $manager = $timeEntry->employee->manager;

            if ($manager) {
                Notification::send($manager, new TimeEntrySubmittedNotification($timeEntry));
            }
        }
    }

    public function updating(TimeEntry $timeEntry): void
    {
        $this->checkAbsenceConflict($timeEntry);
    }

    public function updated(TimeEntry $timeEntry): void
    {
        // Détection du passage à l'état "Approuvé"
        if ($timeEntry->wasChanged('status') && $timeEntry->status === TimeEntryStatus::Approved) {
            $timeEntry->employee->user->notify(new TimeEntryApprovedNotification($timeEntry));
        }
    }

    /**
     * Sécurité d'intégrité : On interdit la suppression d'un pointage validé.
     * En Laravel, retourner 'false' dans l'événement deleting annule l'opération.
     */
    public function deleting(TimeEntry $timeEntry): bool
    {
        if ($timeEntry->status === TimeEntryStatus::Approved) {
            return false;
        }

        return true;
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
                'date' => "Un congé validé existe déjà pour cette date. Saisie d'heures impossible.",
            ]);
        }
    }
}
