<?php

namespace App\Observers\HR;

use App\Enums\HR\TimeEntryStatus;
use App\Models\HR\TimeEntry;
use App\Notifications\HR\TimeEntryApprovedNotification;
use App\Notifications\HR\TimeEntrySubmittedNotification;
use Illuminate\Support\Facades\Notification;

class TimeEntryObserver
{
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
}
