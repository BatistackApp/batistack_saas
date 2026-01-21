<?php

namespace App\Observers\Commerce;

use App\Jobs\Commerce\ComputeDevisAmountsJob;
use App\Models\Commerce\Devis;

class DevisObserver
{
    public function creating(Devis $devis): void
    {
        if (! $devis->number) {
            GenerateDevisNumber::dispatch($devis);
        }
    }

    public function created(Devis $devis): void
    {
        ComputeDevisAmountsJob::dispatch($devis);
    }

    public function updated(Devis $devis): void
    {
        if ($devis->wasChanged('status') && $devis->status->value === 'validated') {
            $devis->tiers->notify(new DevisValidatedNotification($devis));
        }
    }
}
