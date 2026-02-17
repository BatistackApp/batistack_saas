<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class SirenService
{
    public function __construct(private string $baseUrl = '')
    {
        $this->baseUrl = config('services.portail_siren.baseUrl');
    }

    public function fetchCompanyData(string $siret): ?array
    {
        $response = \Http::withToken(config('services.portail_siren.api_key'))
            ->get($this->baseUrl.'/siret?q='.$siret);

        if ($response->failed()) {
            Log::warning("Échec de l'appel à l'API SIRENE pour le SIRET {$siret}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return null;
        }

        $data = $response->json('etablissements');
        $data = $data[0] ?? null;

        return [
            'raison_social' => $data['uniteLegale']['denominationUniteLegale'] ?? null,
            'adresse' => $this->formatAddress($data['adresseEtablissement'] ?? []),
            'code_postal' => $data['adresseEtablissement']['codePostalEtablissement'] ?? null,
            'ville' => $data['adresseEtablissement']['libelleCommuneEtablissement'] ?? null,
            'code_naf' => $data['uniteLegale']['activitePrincipaleUniteLegale'] ?? null,
            'etat_administratif' => $data['uniteLegale']['etatAdministratifUniteLegale'] ?? 'A',
        ];
    }

    /**
     * Vérifie si l'entreprise est toujours active (Recommandation 4)
     */
    public function isStillActive(string $siret): bool
    {
        $data = $this->fetchCompanyData($siret);

        if (! $data) {
            return true;
        }

        return ($data['etat_administratif'] ?? 'A') === 'A';
    }

    private function formatAddress(array $addr): string
    {
        return trim(($addr['numeroVoieEtablissement'] ?? '').' '.($addr['typeVoieEtablissement'] ?? '').' '.($addr['libelleVoieEtablissement'] ?? ''));
    }
}
