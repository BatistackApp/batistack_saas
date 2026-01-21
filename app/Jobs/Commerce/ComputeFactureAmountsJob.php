<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeFactureAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Facture $facture) {}

    public function handle(): void
    {
        $montantHt = $this->facture->lignes()->sum('montant_ht');

        $montantTva = 0;
        foreach ($this->facture->lignes() as $ligne) {
            $tvaRate = $ligne->tva->percentage() / 100;
            $montantTva += $ligne->montant_ht * $tvaRate;
        }

        $this->facture->update([
            'montant_ht' => $montantHt,
            'montant_tva' => round($montantTva, 2),
            'montant_ttc' => round($montantHt + $montantTva, 2),
        ]);
    }
}
