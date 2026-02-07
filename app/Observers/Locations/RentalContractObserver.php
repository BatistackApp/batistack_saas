<?php

namespace App\Observers\Locations;

use App\Enums\Locations\RentalStatus;
use App\Models\Locations\RentalContract;
use Illuminate\Support\Str;

class RentalContractObserver
{
    public function creating(RentalContract $contract): void
    {
        if (empty($contract->reference)) {
            $year = now()->format('Y');
            $latest = RentalContract::where('reference', 'LIKE', "LOC-{$year}-%")
                ->latest('id')
                ->first();

            $number = $latest ? ((int) Str::afterLast($latest->reference, '-') + 1) : 1;
            $contract->reference = "LOC-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
        }
    }

    /**
     * Gère les notifications lors des changements d'état.
     */
    public function updated(RentalContract $contract): void
    {
        // Si le contrat vient d'être activé, on peut notifier le conducteur de travaux
        if ($contract->wasChanged('status') && $contract->status === RentalStatus::ACTIVE) {
            // Logique de notification de début de location
        }
    }
}
