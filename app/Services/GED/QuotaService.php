<?php

namespace App\Services\GED;

use App\Models\Core\Tenants;

class QuotaService
{
    /**
     * Vérifie si le fichier peut être uploadé sans dépasser la limite du plan.
     */
    public function canUpload(Tenants $tenant, int $fileSize): bool
    {
        $limit = $this->getStorageLimit($tenant);
        $used = (int) $tenant->storage_used;

        return ($used + $fileSize) <= $limit;
    }

    public function incrementUsedStorage(Tenants $tenant, int $amount): void
    {
        $tenant->increment('storage_used', $amount);
    }

    public function decrementUsedStorage(Tenants $tenant, int $amount): void
    {
        $tenant->decrement('storage_used', $amount);
    }

    /**
     * Récupère la limite de stockage selon le plan (ou défaut 5GB).
     */
    public function getStorageLimit(Tenants $tenant): int
    {
        return $tenant->plan->storage_limit ?? (5 * 1024 * 1024 * 1024); // 5 Go par défaut
    }

    public function getUsageStats(Tenants $tenant): array
    {
        $limit = $this->getStorageLimit($tenant);
        $used = (int) $tenant->storage_used;

        return [
            'used_bytes' => $used,
            'limit_bytes' => $limit,
            'used_human' => $this->formatBytes($used),
            'limit_human' => $this->formatBytes($limit),
            'percentage' => $limit > 0 ? round(($used / $limit) * 100, 2) : 0,
            'remaining_bytes' => max(0, $limit - $used),
        ];
    }

    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, 2).' '.$units[$pow];
    }
}
