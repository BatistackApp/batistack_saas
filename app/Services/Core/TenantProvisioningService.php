<?php

namespace App\Services\Core;

use App\Models\Core\ModuleCatalog;
use App\Models\Core\TenantModule;
use App\Models\Core\Tenants;
use DB;

class TenantProvisioningService
{
    public function __construct(
        private OvhDomainService $ovhDomainService,
        private TenantDatabaseService $databaseService,
    ) {}

    public function provision(array $data): Tenants
    {
        return DB::transaction(function () use ($data) {
            // 1. Créer le tenant
            $tenant = Tenants::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'database' => "tenant_{$data['slug']}",
                'domain' => $data['custom_domain'] ?? null,
                'status' => \App\Enums\Core\TenantStatus::Active,
                'activated_at' => now(),
                'settings' => $data['settings'] ?? [],
            ]);

            // 2. Déclarer le sous-domaine (OVH en production)
            if ($this->shouldDeclareDomain()) {
                $this->ovhDomainService->createSubdomain(
                    slug: $tenant->slug,
                    tenantId: $tenant->id,
                );
            }

            // 3. Créer le schéma BD tenant
            $this->databaseService->createSchema($tenant->database);

            // 4. Migrer les tables tenant-spécifiques
            $this->databaseService->migrateSchema($tenant->database);

            // 5. Activer les 8 modules Core
            $this->activateCoreModules($tenant);

            // 6. Seeder les données de base (Plan comptable, Journaux)
            $this->databaseService->seedTenantData($tenant);

            return $tenant;
        });
    }

    private function activateCoreModules(Tenants $tenant): void
    {
        ModuleCatalog::where('is_core', true)
            ->each(function (ModuleCatalog $module) use ($tenant) {
                TenantModule::create([
                    'tenant_id' => $tenant->id,
                    'module_id' => $module->id,
                    'status' => \App\Enums\Core\TenantModuleStatus::Active->value,
                    'starts_at' => now(),
                    'ends_at' => null,
                ]);
            });
    }

    private function shouldDeclareDomain(): bool
    {
        return app()->isProduction();
    }
}
