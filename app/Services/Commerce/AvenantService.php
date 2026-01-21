<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Models\Commerce\Avenant;
use App\Models\Commerce\AvenantLigne;

class AvenantService
{
    public function __construct(
        private NumberGeneratorService $numberGenerator,
        private CalculService $calcul,
    ) {}

    public function create(array $data): Avenant
    {
        $data['number'] = $this->numberGenerator->generateAvenantNumber($data['tenant_id']);
        $data['status'] = DocumentStatus::Draft;

        $avenant = Avenant::create($data);

        return $avenant->load('lignes');
    }

    public function addLigne(Avenant $avenant, array $data): AvenantLigne
    {
        $data['montant_ht'] = $this->calcul->calculateMontantHT(
            $data['quantite'],
            $data['prix_unitaire']
        );

        $ligne = $avenant->lignes()->create($data);

        $this->recalculateTotals($avenant);

        return $ligne;
    }

    public function removeLigne(AvenantLigne $ligne): void
    {
        $avenant = $ligne->avenant;
        $ligne->delete();

        $this->recalculateTotals($avenant);
    }

    public function recalculateTotals(Avenant $avenant): void
    {
        $avenant->load('lignes');

        $montantHT = $avenant->lignes->sum('montant_ht');
        $montantTVA = $this->calcul->calculateTotalTVA($avenant->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $avenant->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }

    public function validate(Avenant $avenant): Avenant
    {
        $avenant->update(['status' => DocumentStatus::Validated]);

        return $avenant->refresh();
    }

    public function cancel(Avenant $avenant): Avenant
    {
        $avenant->update(['status' => DocumentStatus::Cancelled]);

        return $avenant->refresh();
    }
}
