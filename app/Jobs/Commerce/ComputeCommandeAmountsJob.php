<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Commande;
use App\Services\Commerce\CalculService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeCommandeAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Commande $commande) {}

    public function handle(CalculService $calcul): void
    {
        $this->commande->load('lignes');

        $montantHT = $this->commande->lignes->sum('montant_ht');
        $montantTVA = $calcul->calculateTotalTVA($this->commande->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $this->commande->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }
}
