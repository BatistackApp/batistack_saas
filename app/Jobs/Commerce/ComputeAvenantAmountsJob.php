<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Avenant;
use App\Services\Commerce\CalculService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeAvenantAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Avenant $avenant)
    {
    }

    public function handle(CalculService $calcul): void
    {
        $this->avenant->load('lignes');

        $montantHT = $this->avenant->lignes->sum('montant_ht');
        $montantTVA = $calcul->calculateTotalTVA($this->avenant->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $this->avenant->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }
}
