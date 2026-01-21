<?php

namespace App\Observers\Commerce;

use App\Jobs\Commerce\ComputeCommandeAmountsJob;
use App\Jobs\Commerce\GenerateCommandeNumberJob;
use App\Models\Commerce\Commande;

class CommandeObserver
{
    public function creating(Commande $commande): void
    {
        if (! $commande->number) {
            GenerateCommandeNumberJob::dispatch($commande);
        }
    }

    public function created(Commande $commande): void
    {
        ComputeCommandeAmountsJob::dispatch($commande);
    }

    public function updated(Commande $commande): void
    {
        if ($commande->isDirty(['lignes'])) {
            ComputeCommandeAmountsJob::dispatch($commande);
        }
    }
}
