<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Models\Commerce\Avoir;
use App\Models\Commerce\AvoirLigne;

class AvoirService
{
    public function __construct(
        private NumberGeneratorService $numberGenerator,
        private CalculService $calcul,
    ) {}

    public function create(array $data): Avoir
    {
        $data['number'] = $this->numberGenerator->generateAvoirNumber($data['tenant_id']);
        $data['status'] = DocumentStatus::Draft;

        $avoir = Avoir::create($data);

        return $avoir->load('lignes');
    }

    public function addLigne(Avoir $avoir, array $data): AvoirLigne
    {
        $data['montant_ht'] = $this->calcul->calculateMontantHT(
            $data['quantite'],
            $data['prix_unitaire']
        );

        $ligne = $avoir->lignes()->create($data);

        $this->recalculateTotals($avoir);

        return $ligne;
    }

    public function removeLigne(AvoirLigne $ligne): void
    {
        $avoir = $ligne->avoir;
        $ligne->delete();

        $this->recalculateTotals($avoir);
    }

    public function recalculateTotals(Avoir $avoir): void
    {
        $avoir->load('lignes');

        $montantHT = $avoir->lignes->sum('montant_ht');
        $montantTVA = $this->calcul->calculateTotalTVA($avoir->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $avoir->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }

    public function validate(Avoir $avoir): Avoir
    {
        $avoir->update(['status' => DocumentStatus::Validated]);

        return $avoir->refresh();
    }

    public function cancel(Avoir $avoir): Avoir
    {
        $avoir->update(['status' => DocumentStatus::Cancelled]);

        return $avoir->refresh();
    }
}
