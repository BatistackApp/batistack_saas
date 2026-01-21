<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Devis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeDevisAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Devis $devis) {}

    public function handle(): void
    {
        $montantHt = $this->devis->lignes()->sum('montant_ht');

        $montantTva = 0;
        foreach ($this->devis->lignes() as $ligne) {
            $tvaRate = $ligne->tva->percentage() / 100;
            $montantTva += $ligne->montant_ht * $tvaRate;
        }

        $this->devis->update([
            'montant_ht' => $montantHt,
            'montant_tva' => round($montantTva, 2),
            'montant_ttc' => round($montantHt + $montantTva, 2),
        ]);
    }
}
