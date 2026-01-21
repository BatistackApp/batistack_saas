<?php

namespace App\Observers\Commerce;

use App\Jobs\Commerce\AccountFactureJob;
use App\Jobs\Commerce\ComputeFactureAmountsJob;
use App\Jobs\Commerce\GenerateFactureNumberJob;
use App\Models\Commerce\Facture;

class FactureObserver
{
    public function creating(Facture $facture): void
    {
        if (! $facture->number) {
            GenerateFactureNumberJob::dispatch($facture);
        }
    }

    public function created(Facture $facture): void
    {
        ComputeFactureAmountsJob::dispatch($facture);
    }

    public function updating(Facture $facture): void
    {
        if ($facture->isDirty(['montant_ht', 'montant_tva', 'montant_ttc'])) {
            ComputeFactureAmountsJob::dispatch($facture);
        }
    }

    public function updated(Facture $facture): void
    {
        if ($facture->wasChanged('status') && $facture->status->value === 'validated') {
            AccountFactureJob::dispatch($facture);
        }
    }

    public function deleting(Facture $facture): void
    {
        if ($facture->reglements()->exists()) {
            throw new \Exception('Impossible de supprimer une facture avec des règlements');
        }
    }
}
