<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FuelCardApiService
{
    /**
     * Simule l'appel à une API externe de type TotalEnergies ou Shell.
     */
    public function fetchTransactions(string $cardId, ?string $since = null): array
    {
        try {
            $apiKey = ''; // Fourni par Batistack en runtime

            // En production, nous pourrions gérer ici différents drivers selon le fournisseur
            $response = Http::withToken($apiKey)
                ->get("https://api.fuel-provider.com/v1/cards/{$cardId}/transactions", [
                    'from' => $since ?? now()->subDays(30)->format('Y-m-d'),
                ]);

            return $response->successful() ? $response->json('data') : [];
        } catch (Exception $e) {
            Log::error("Erreur API Carburant [Card: {$cardId}]: ".$e->getMessage());

            return [];
        }
    }
}
