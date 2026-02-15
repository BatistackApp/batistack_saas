<?php

namespace App\Services\Fleet;

use App\Models\Fleet\Vehicle;
use App\Models\Tiers\TierQualification;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FleetComplianceService
{
    /**
     * Vérifie si un utilisateur est apte à conduire un véhicule spécifique à une date donnée.
     * Retourne un tableau avec 'status' (bool) et 'message' (string|null).
     */
    public function checkDriverCompliance(Vehicle $vehicle, User $user, ?CarbonImmutable $date = null): array
    {
        $date ??= now();
        $requiredCert = $vehicle->required_certification_type;

        // 1. Si aucune certification n'est requise, c'est conforme par défaut
        if (empty($requiredCert)) {
            return [
                'status' => true,
                'message' => null
            ];
        }

        // 2. Récupération du profil Tiers de l'utilisateur
        // On suppose ici que le User est lié à un Tiers (via la relation users() définie dans le modèle Tiers)
        $tier = $user->tiers;

        if (!$tier) {
            return [
                'status' => false,
                'message' => "L'utilisateur n'est pas lié à un profil de tiers. Impossible de vérifier ses habilitations."
            ];
        }

        // 3. Recherche de la qualification correspondante
        $qualification = TierQualification::where('tiers_id', $tier->id)
            ->where('label', $requiredCert)
            ->first();

        if (!$qualification) {
            return [
                'status' => false,
                'message' => "Le conducteur ne possède pas l'habilitation requise : {$requiredCert}."
            ];
        }

        // 4. Vérification de la date de validité
        if ($qualification->valid_until && $qualification->valid_until->isBefore($date)) {
            return [
                'status' => false,
                'message' => "L'habilitation {$requiredCert} a expiré le " . $qualification->valid_until->format('d/m/Y') . "."
            ];
        }

        return [
            'status' => true,
            'message' => "Habilitation {$requiredCert} valide."
        ];
    }

    /**
     * Retourne la liste des conducteurs (Users) éligibles pour un véhicule donné.
     * Utile pour filtrer les menus déroulants dans l'UI.
     */
    public function getEligibleDriversForVehicle(Vehicle $vehicle): User
    {
        $requiredCert = $vehicle->required_certification_type;

        if (empty($requiredCert)) {
            return User::where('tenants_id', $vehicle->tenants_id)->get();
        }

        // On cherche les tiers qui ont la qualification valide
        $eligibleTierIds = TierQualification::where('label', $requiredCert)
            ->where(function($query) {
                $query->whereNull('valid_until')
                    ->orWhere('valid_until', '>=', now());
            })
            ->pluck('tiers_id');

        // On retourne les utilisateurs liés à ces tiers
        return User::whereIn('tiers_id', $eligibleTierIds) // Assumer la colonne tiers_id sur User ou via relation
        ->where('tenants_id', $vehicle->tenants_id)
            ->get();
    }
}
