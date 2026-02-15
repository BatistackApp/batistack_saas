<?php

namespace App\Observers\Fleet;

use App\Jobs\Fleet\ProcessFineMatchingJob;
use App\Models\Fleet\VehicleFine;
use App\Notifications\Fleet\FineAssignedNotification;

class VehicleFineObserver
{
    public function created(VehicleFine $fine): void
    {
        ProcessFineMatchingJob::dispatch($fine);
    }

    public function updated(VehicleFine $fine): void
    {
        // Si le user_id vient d'être renseigné (Désignation chauffeur)
        if ($fine->wasChanged('user_id') && $fine->user_id !== null) {
            $fine->user->notify(new FineAssignedNotification($fine));
        }
    }
}
