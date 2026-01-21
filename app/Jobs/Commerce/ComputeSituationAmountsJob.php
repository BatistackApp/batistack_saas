<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Situation;
use App\Services\Commerce\CalculService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeSituationAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Situation $situation)
    {
    }

    public function handle(CalculService $calcul): void
    {
        $this->situation->load('lignes');

        $montantHT = $this->situation->lignes->sum('montant_ht');
        $montantTVA = $calcul->calculateTotalTVA($this->situation->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $this->situation->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }
}
