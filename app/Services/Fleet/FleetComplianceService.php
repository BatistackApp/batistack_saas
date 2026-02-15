<?php

namespace App\Services\Fleet;

use App\Enums\Tiers\TierDocumentStatus;
use App\Enums\Tiers\TierDocumentType;
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
        $errors = [];

        // Récupération du profil "Tiers" associé au User (conducteur)
        // On suppose que l'utilisateur est lié à un Tiers de type 'personne_physique' (Employé)
        $tier = $user->tiers; // Relation à définir ou via un repo

        if (!$tier) {
            return [
                'status' => false,
                'message' => 'Profil de conformité (Tiers) manquant pour cet utilisateur.',
                'errors' => ['user_id' => ['Profil de conformité introuvable.']]
            ];
        }

        // 1. VÉRIFICATION SYSTÉMATIQUE DU PERMIS DE CONDUIRE
        $license = $tier->documents()
            ->where('type', TierDocumentType::DRIVERLICENCE) // Type de document générique
            ->where('status', TierDocumentStatus::Valid)
            ->where('expires_at', '>', now())
            ->first();

        if (!$license) {
            return [
                'status' => false,
                'message' => 'Profil de conformité (Tiers) manquant pour cet utilisateur.',
                'errors' => ['user_id' => ['Le permis de conduire est manquant, expiré ou non validé.']]
            ];
        }

        // 2. VÉRIFICATION DES CERTIFICATIONS SPÉCIFIQUES (ex: CACES, Habilitation)
        if ($vehicle->required_certification_type) {
            $hasQualification = $tier->qualifications()
                ->where('label', $vehicle->required_certification_type)
                ->where('valid_until', '>', now())
                ->exists();

            if (!$hasQualification) {
                return [
                    'status' => false,
                    'message' => 'Profil de conformité (Tiers) manquant pour cet utilisateur.',
                    'errors' => ['user_id' => ["La qualification spécifique '{$vehicle->required_certification_type}' est requise pour ce véhicule."]]
                ];
            }
        }

        return [
            'status' => true,
            'message' => 'Habilitation valide.'
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
