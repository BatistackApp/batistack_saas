<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Log;
use Symfony\Component\HttpFoundation\Response;

class TenantRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $role): Response
    {
        $user = $request->user();

        // 1. Vérifie si l'utilisateur est authentifié
        if (! $user) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // 2. Vérifie le rôle via Spatie
        if (! $user->hasRole($role)) {
            return response()->json(['message' => 'Forbidden: Missing Role.'], 403);
        }

        // 3. Sécurité multi-tenant : Vérification de l'appartenance au Tenant courant
        // Même si le middleware global a fait son travail, une double vérification prévient les fuites de données.
        // On récupère l'ID du tenant actif via la session ou le service de contexte
        $activeTenantId = session('active_tenants_id');

        if ($activeTenantId && $user->tenants_id !== (int) $activeTenantId) {
            // Tentative d'accès inter-tenant détectée : Logging de sécurité impératif
            Log::critical("ALERTE SÉCURITÉ : Tentative d'accès inter-tenant par l'utilisateur ID {$user->id}. ".
                "Tenant Utilisateur : {$user->tenants_id} | Tenant Contextuel : {$activeTenantId}");

            return response()->json(['message' => 'Unauthorized: Tenant context mismatch.'], 403);
        }

        return $next($request);
    }
}
