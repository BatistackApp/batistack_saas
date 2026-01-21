<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Facture;
use App\Services\Commerce\CalculService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeFactureAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Facture $facture) {}

    public function handle(CalculService $calcul): void
    {
        $this->facture->load('lignes', 'reglements');

        $montantHT = $this->facture->lignes->sum('montant_ht');
        $montantTVA = $calcul->calculateTotalTVA($this->facture->lignes);
        $montantTTC = $montantHT + $montantTVA;
        $montantPaye = $this->facture->reglements->sum('montant');

        $this->facture->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
            'montant_paye' => $montantPaye,
        ]);
    }
}
