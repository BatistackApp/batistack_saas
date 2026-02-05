<?php

namespace App\Exceptions\GED;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class QuotaExceededException extends Exception
{
    /**
     * @param int $currentUsage Taille actuelle utilisée en octets
     * @param int $requestedSize Taille du fichier qu'on tente d'uploader
     * @param int $limit Limite totale autorisée par le plan du Tenant
     */
    public function __construct(
        protected int $currentUsage,
        protected int $requestedSize,
        protected int $limit
    ) {
        $message = "Quota de stockage dépassé. Disponible: " . ($limit - $currentUsage) . " octets.";
        parent::__construct($message, 403);
    }

    /**
     * Reporte l'exception dans les logs (optionnel).
     */
    public function report(): void
    {
        \Log::warning('Tentative d\'upload bloquée par quota', [
            'current_usage' => $this->currentUsage,
            'attempted_size' => $this->requestedSize,
            'limit' => $this->limit,
            'tenant_id' => auth()->user()?->tenants_id,
        ]);
    }

    /**
     * Transforme l'exception en réponse HTTP JSON.
     * Idéal pour les intégrations Frontend (React/Vue).
     */
    public function render(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'STORAGE_QUOTA_EXCEEDED',
            'message' => 'Votre espace de stockage est insuffisant pour ce fichier.',
            'context' => [
                'usage_formatted' => $this->formatBytes($this->currentUsage),
                'limit_formatted' => $this->formatBytes($this->limit),
                'file_size' => $this->formatBytes($this->requestedSize),
                'upgrade_required' => true
            ]
        ], 403);
    }

    /**
     * Utilitaire interne pour rendre le message lisible.
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
