<?php

namespace App\Services;

class SirenService
{
    public function __construct(private string $baseUrl = 'https://api.insee.fr') {}

    public function fetchCompanyData(string $siret): ?array
    {
        $response = \Http::withToken(config('services.portail_siren.api_key'))
            ->get($this->baseUrl.'/siret?q='.$siret);

        if ($response->failed()) {
            return null;
        }

        $data = $response->json('etablissements');
        $data = $data[0] ?? null;

        return [
            'raison_social' => $data['uniteLegale']['denominationUniteLegale'] ?? null,
            'adresse' => $this->formatAddress($data['adresseEtablissement']),
            'code_postal' => $data['adresseEtablissement']['codePostalEtablissement'] ?? null,
            'ville' => $data['adresseEtablissement']['libelleCommuneEtablissement'] ?? null,
            'code_naf' => $data['uniteLegale']['activitePrincipaleUniteLegale'] ?? null,
        ];
    }

    private function formatAddress(array $addr): string
    {
        return trim(($addr['numeroVoieEtablissement'] ?? '').' '.($addr['typeVoieEtablissement'] ?? '').' '.($addr['libelleVoieEtablissement'] ?? ''));
    }
}
