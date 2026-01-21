<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\TaxRate;
use Illuminate\Database\Eloquent\Collection;

class CalculService
{
    public function calculateMontantHT(float|int $quantite, float|int $prixUnitaire): float
    {
        return round($quantite * $prixUnitaire, 2);
    }

    public function calculateMontantHTFromPercentage(float|int $percentage, float|int $prixUnitaire): float
    {
        return round(($percentage / 100) * $prixUnitaire, 2);
    }

    public function calculateTVA(float|int $montantHT, TaxRate $taxRate): float
    {
        return round($montantHT * ($taxRate->percentage() / 100), 2);
    }

    public function calculateTotalTVA(Collection $lignes): float
    {
        return $lignes->sum(function ($ligne) {
            $taxRate = $ligne->tva instanceof TaxRate ? $ligne->tva : TaxRate::from($ligne->tva);
            return $this->calculateTVA($ligne->montant_ht, $taxRate);
        });
    }

    public function calculateMontantTTC(float|int $montantHT, float|int $montantTVA): float
    {
        return round($montantHT + $montantTVA, 2);
    }
}
