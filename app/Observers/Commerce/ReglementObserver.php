<?php

namespace App\Observers\Commerce;

use App\Jobs\Commerce\UpdateFacturePaymentStatusJob;
use App\Models\Commerce\Reglement;
use App\Notifications\Commerce\ReglementReceivedNotification;

class ReglementObserver
{
    public function creating(Reglement $reglement): void
    {
        UpdateFacturePaymentStatusJob::dispatch($reglement->facture);
        $reglement->facture->tiers->notify(new ReglementReceivedNotification($reglement));
    }

    public function deleting(Reglement $reglement): void
    {
        UpdateFacturePaymentStatusJob::dispatch($reglement->facture);
    }
}
