<?php

namespace App\Http\Controllers\Banque;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;
use Log;

class BridgeWebhookController extends Controller
{
    /**
     * Reçoit les webhooks de Bridge (refresh de compte, expiration de consentement).
     */
    public function handle(Request $request): JsonResponse
    {
        // Dans une version de production, on validerait la signature ici
        $event = $request->input('type');
        $resourceId = $request->input('resource_id');

        Log::info("Bridge Webhook V3 reçu : {$event} sur la ressource {$resourceId}");

        switch ($event) {
            case 'item.refreshed':
                // Déclencher un job de synchro pour tous les comptes liés à cet item
                break;

            case 'item.consent_expired':
                // Mettre à jour les statuts et notifier l'utilisateur
                break;
        }

        return response()->json(['status' => 'accepted']);
    }
}
