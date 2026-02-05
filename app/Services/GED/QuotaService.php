<?php

namespace App\Services\GED;

use App\Models\Core\Tenants;
use DB;

class QuotaService
{
    const int BASE_QUOTA_BYTES = 1073741824;

    /**
     * Calcule l'utilisation actuelle du stockage pour un Tenant.
     */
    public function getUsedStorage(int $tenantId): int
    {
        return (int) DB::table('documents')
            ->where('tenant_id', $tenantId)
            ->whereNull('deleted_at')
            ->sum('size');
    }

    /**
     * Récupère la limite autorisée pour le Tenant.
     * Intègre la logique de vérification des abonnements supplémentaires.
     */
    public function getStorageLimit(Tenants $tenant): int
    {
        // Logique métier : On vérifie si le tenant possède une option "extra_storage"
        // Ceci est un exemple d'intégration avec le module Core/SAAS
        $extraLimit = 0;

        if (isset($tenant->options['extra_storage_gb'])) {
            $extraLimit = $tenant->options['extra_storage_gb'] * 1024 * 1024 * 1024;
        }

        return self::BASE_QUOTA_BYTES + $extraLimit;
    }

    /**
     * Vérifie si un nouveau fichier peut être ajouté.
     */
    public function canUpload(Tenants $tenant, int $newFileSize): bool
    {
        $used = $this->getUsedStorage($tenant->id);
        $limit = $this->getStorageLimit($tenant);

        return ($used + $newFileSize) <= $limit;
    }

    /**
     * Retourne le pourcentage d'utilisation.
     */
    public function getUsagePercentage(Tenants $tenant): float
    {
        $limit = $this->getStorageLimit($tenant);
        if ($limit === 0) return 0;

        return round(($this->getUsedStorage($tenant->id) / $limit) * 100, 2);
    }
}
