<?php

namespace App\Services\Commerce;

use App\Enums\Commerce\CommandeStatus;
use App\Models\Commerce\Commande;
use App\Models\Commerce\CommandeLigne;

class CommandeService
{
    public function __construct(
        private NumberGeneratorService $numberGenerator,
        private CalculService $calcul,
    ) {}

    public function create(array $data): Commande
    {
        $data['number'] = $this->numberGenerator->generateCommandeNumber($data['tenant_id']);
        $data['status'] = CommandeStatus::Draft;

        $commande = Commande::create($data);

        return $commande->load('lignes');
    }

    public function addLigne(Commande $commande, array $data): CommandeLigne
    {
        $data['montant_ht'] = $this->calcul->calculateMontantHT(
            $data['quantite_commande'],
            $data['prix_unitaire']
        );

        $ligne = $commande->lignes()->create($data);

        $this->recalculateTotals($commande);

        return $ligne;
    }

    public function removeLigne(CommandeLigne $ligne): void
    {
        $commande = $ligne->commande;
        $ligne->delete();

        $this->recalculateTotals($commande);
    }

    public function updateDeliveryQuantity(CommandeLigne $ligne, float $quantiteLivree): CommandeLigne
    {
        $ligne->update(['quantite_livre' => $quantiteLivree]);

        $this->updateCommandeStatus($ligne->commande);

        return $ligne->refresh();
    }

    public function recalculateTotals(Commande $commande): void
    {
        $commande->load('lignes');

        $montantHT = $commande->lignes->sum('montant_ht');
        $montantTVA = $this->calcul->calculateTotalTVA($commande->lignes);
        $montantTTC = $montantHT + $montantTVA;

        $commande->update([
            'montant_ht' => $montantHT,
            'montant_tva' => $montantTVA,
            'montant_ttc' => $montantTTC,
        ]);
    }

    public function confirm(Commande $commande): Commande
    {
        $commande->update(['status' => CommandeStatus::Confirmed]);

        return $commande->refresh();
    }

    public function cancel(Commande $commande): Commande
    {
        $commande->update(['status' => CommandeStatus::Cancelled]);

        return $commande->refresh();
    }

    private function updateCommandeStatus(Commande $commande): void
    {
        $commande->load('lignes');

        $totalCommande = $commande->lignes->sum('quantite_commande');
        $totalLivree = $commande->lignes->sum('quantite_livre');

        if ($totalLivree === 0.0) {
            $status = CommandeStatus::Confirmed;
        } elseif ($totalLivree < $totalCommande) {
            $status = CommandeStatus::PartiallyDelivered;
        } else {
            $status = CommandeStatus::Delivered;
        }

        $commande->update(['status' => $status]);
    }
}
