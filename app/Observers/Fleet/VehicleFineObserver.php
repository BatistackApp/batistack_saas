<?php

namespace App\Observers\Fleet;

use App\Enums\Fleet\DesignationStatus;
use App\Enums\Fleet\FinesStatus;
use App\Jobs\Fleet\ProcessFineMatchingJob;
use App\Models\Fleet\VehicleFine;
use App\Notifications\Fleet\FineAssignedNotification;

class VehicleFineObserver
{
    public function creating(VehicleFine $fine): void
    {
        if(empty($fine->due_date)) {
            $fine->due_date = $fine->offense_at->addDays(45);
        }
    }
    public function created(VehicleFine $fine): void
    {
        $fine->updateQuietly([
            'designation_status' => DesignationStatus::Pending,
        ]);
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
