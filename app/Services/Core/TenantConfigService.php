<?php

namespace App\Services\Core;

use App\Models\Core\Tenants;

class TenantConfigService
{
    /**
     * Récupère une configuration spécifique pour un tenant.
     *
     * @param  string  $key  Clé de configuration (ex: 'commerce.default_vat')
     * @param  mixed  $default  Valeur par défaut si non définie
     */
    public static function get(Tenants $tenant, string $key, mixed $default = null): mixed
    {
        $settings = $tenant->settings ?? [];

        // Utilisation de data_get pour accéder facilement au JSON imbriqué
        return data_get($settings, $key, $default);
    }

    /**
     * Définit les valeurs par défaut du système
     */
    public static function defaults(): array
    {
        return [
            'commerce' => [
                'default_vat_rate' => 20.00,
                'currency' => 'EUR',
                'due_date_days' => 30,
                'retenue_garantie_default_pct' => 5.00,
                'quotes' => [
                    'validity_months' => 1,
                ],
            ],
            'inventory' => [
                'low_stock_threshold' => 10,
            ],
        ];
    }
}
