<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Log;

class UrssafApiService
{
    public function __construct(private string $baseUrl = '')
    {
        $this->baseUrl = config('services.portail_urssaf.baseUrl');
    }

    /**
     * Vérifie l'authenticité d'une attestation de vigilance.
     * * @param string $siret Le SIRET de l'entreprise à vérifier
     * @param string $verificationKey Le code de sécurité présent sur l'attestation
     * @return bool
     */
    public function verifyAttestation(string $siret, string $verificationKey): bool {
        try {
            // L'API requiert une authentification via un Token OAuth2
            // Les credentials doivent être configurés dans config/services.php
            $response = Http::acceptJson()
                ->get($this->baseUrl, [
                    'siret' => preg_replace('/\D/', '', $siret),
                    'codeSecurite' => $verificationKey
                ]);

            if ($response->successful()) {
                // Selon la doc, l'état 'VALIDE' confirme l'authenticité
                return $response->json('etat') === 'VALIDE';
            }

            Log::warning("Échec de vérification URSSAF pour le SIRET {$siret} : " . $response->body());
            return false;
        } catch (\Exception $e) {
            Log::error("Erreur de connexion à l'API URSSAF : " . $e->getMessage());
            return false;
        }
    }
}
