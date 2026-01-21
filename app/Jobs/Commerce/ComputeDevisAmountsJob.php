<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Devis;
use App\Services\Commerce\CalculService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ComputeDevisAmountsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Devis $devis) {}

    public function handle(CalculService $calcul): void
    {
        $this->devis->load('lignes');

        $montantHT = $this->devis->lignes->sum('montant_ht');
        $montantTVA = $calcul->calculateTotalTVA($this->devis->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $this->devis->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }
}
