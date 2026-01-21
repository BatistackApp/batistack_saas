<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Avoir;
use App\Services\Commerce\CalculService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeAvoirAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Avoir $avoir)
    {
    }

    public function handle(CalculService $calcul): void
    {
        $this->avoir->load('lignes');

        $montantHT = $this->avoir->lignes->sum('montant_ht');
        $montantTVA = $calcul->calculateTotalTVA($this->avoir->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $this->avoir->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }
}
