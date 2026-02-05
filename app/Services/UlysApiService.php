<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UlysApiService
{
    /**
     * Simule l'appel à une API externe de type Ulys/Vinci.
     */
    public function fetchTollTrips(string $tagId): array
    {
        try {
            $response = Http::withToken('')
                ->get("https://api.toll-provider.com/v1/tags/{$tagId}/trips");

            return $response->successful() ? $response->json('trips') : [];
        } catch (Exception $e) {
            Log::error("Erreur API Péage [Tag: {$tagId}]: ".$e->getMessage());

            return [];
        }
    }
}
