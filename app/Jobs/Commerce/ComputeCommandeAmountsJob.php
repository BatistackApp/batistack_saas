<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeCommandeAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Commande $commande) {}

    public function handle(): void
    {
        $montantHt = $this->commande->lignes()->sum('montant_ht');

        $montantTva = 0;
        foreach ($this->commande->lignes() as $ligne) {
            $tvaRate = $ligne->tva->percentage() / 100;
            $montantTva += $ligne->montant_ht * $tvaRate;
        }

        $this->commande->update([
            'montant_ht' => $montantHt,
            'montant_tva' => round($montantTva, 2),
            'montant_ttc' => round($montantHt + $montantTva, 2),
        ]);
    }
}
