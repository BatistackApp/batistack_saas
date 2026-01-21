<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\DocumentStatus;
use App\Enums\Commerce\FactureType;
use App\Models\Commerce\Facture;
use App\Models\Commerce\FactureLigne;
use App\Models\Commerce\Reglement;

class FactureService
{
    public function __construct(
        private NumberGeneratorService $numberGenerator,
        private CalculService $calcul,
    ) {}

    public function create(array $data): Facture
    {
        $data['number'] = $this->numberGenerator->generateFactureNumber($data['tenant_id']);
        $data['type'] = $data['type'] ?? FactureType::Standard;

        $facture = Facture::create($data);

        return $facture->load('lignes', 'reglements');
    }

    public function addLigne(Facture $facture, array $data): FactureLigne
    {
        $data['montant_ht'] = $this->calcul->calculateMontantHT(
            $data['quantite'],
            $data['prix_unitaire']
        );

        $ligne = $facture->lignes()->create($data);

        $this->recalculateTotals($facture);

        return $ligne;
    }

    public function removeLigne(FactureLigne $ligne): void
    {
        $facture = $ligne->facture;
        $ligne->delete();

        $this->recalculateTotals($facture);
    }

    public function recalculateTotals(Facture $facture): void
    {
        $facture->load('lignes', 'reglements');

        $montantHT = $facture->lignes->sum('montant_ht');
        $montantTVA = $this->calcul->calculateTotalTVA($facture->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $montantPaye = $facture->reglements->sum('montant');

        $facture->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
            'montant_paye' => $montantPaye,
        ]);

        $this->updateFactureStatus($facture);
    }

    public function addPayment(Facture $facture, array $data): Reglement
    {
        $reglement = $facture->reglements()->create($data);

        $this->recalculateTotals($facture);

        return $reglement;
    }

    public function validate(Facture $facture): Facture
    {
        $facture->update(['status' => DocumentStatus::Validated]);

        return $facture->refresh();
    }

    public function cancel(Facture $facture): Facture
    {
        $facture->update(['status' => DocumentStatus::Cancelled]);

        return $facture->refresh();
    }

    private function updateFactureStatus(Facture $facture): void
    {
        if ($facture->montant_ttc == 0) {
            return;
        }

        if ($facture->montant_paye == 0) {
            $status = DocumentStatus::Invoiced;
        } elseif ($facture->montant_paye < $facture->montant_ttc) {
            $status = DocumentStatus::PartiallyPaid;
        } else {
            $status = DocumentStatus::Paid;
        }

        $facture->update(['status' => $status]);
    }
}
