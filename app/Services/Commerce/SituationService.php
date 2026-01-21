<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Models\Commerce\Situation;
use App\Models\Commerce\SituationLigne;

class SituationService
{
    public function __construct(
        private NumberGeneratorService $numberGenerator,
        private CalculService $calcul,
    ) {}

    public function create(array $data): Situation
    {
        $data['number'] = $this->numberGenerator->generateSituationNumber($data['tenant_id']);

        $situation = Situation::create($data);

        return $situation->load('lignes');
    }

    public function addLigne(Situation $situation, array $data): SituationLigne
    {
        $montantHT = $this->calcul->calculateMontantHTFromPercentage(
            $data['percentage_avancement'],
            $data['prix_unitaire']
        );

        $data['montant_ht'] = $montantHT;

        $ligne = $situation->lignes()->create($data);

        $this->recalculateTotals($situation);

        return $ligne;
    }

    public function removeLigne(SituationLigne $ligne): void
    {
        $situation = $ligne->situation;
        $ligne->delete();

        $this->recalculateTotals($situation);
    }

    public function recalculateTotals(Situation $situation): void
    {
        $situation->load('lignes');

        $montantHT = $situation->lignes->sum('montant_ht');
        $montantTVA = $this->calcul->calculateTotalTVA($situation->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $situation->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }

    public function validate(Situation $situation): Situation
    {
        $situation->update(['status' => DocumentStatus::Validated]);

        return $situation->refresh();
    }

    public function cancel(Situation $situation): Situation
    {
        $situation->update(['status' => DocumentStatus::Cancelled]);

        return $situation->refresh();
    }
}
