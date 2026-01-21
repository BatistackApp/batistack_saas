<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Models\Commerce\Devis;
use App\Models\Commerce\DevisLigne;

class DevisService
{
    public function __construct(
        private NumberGeneratorService $numberGenerator,
        private CalculService $calcul,
    ) {}

    public function create(array $data): Devis
    {
        $data['number'] = $this->numberGenerator->generateDevisNumber($data['tenant_id']);

        $devis = Devis::create($data);

        return $devis->load('lignes');
    }

    public function addLigne(Devis $devis, array $data): DevisLigne
    {
        $data['montant_ht'] = $this->calcul->calculateMontantHT(
            $data['quantite'],
            $data['prix_unitaire']
        );

        $ligne = $devis->lignes()->create($data);

        $this->recalculateTotals($devis);

        return $ligne;
    }

    public function removeLigne(DevisLigne $ligne): void
    {
        $devis = $ligne->devis;
        $ligne->delete();

        $this->recalculateTotals($devis);
    }

    public function recalculateTotals(Devis $devis): void
    {
        $devis->load('lignes');

        $montantHT = $devis->lignes->sum('montant_ht');
        $montantTVA = $this->calcul->calculateTotalTVA($devis->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $devis->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }

    public function validate(Devis $devis): Devis
    {
        $devis->update(['status' => DocumentStatus::Validated]);

        return $devis->refresh();
    }

    public function cancel(Devis $devis): Devis
    {
        $devis->update(['status' => DocumentStatus::Cancelled]);

        return $devis->refresh();
    }
}
