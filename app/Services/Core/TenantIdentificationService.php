<?php

namespace App\Services\Core;

use App\Models\Core\Tenants;
use Cache;

class TenantIdentificationService
{
    public function identifyFromRequest(): ?Tenants
    {
        $host = request()->getHost();

        return Cache::remember("tenant:host:{$host}", ttl: 86400, callback: function () use ($host) {
            // Cas 1 : Domaine personnalisé (domain = custom_domain)
            if ($tenant = Tenants::where('domain', $host)->first()) {
                return $tenant;
            }

            // Cas 2 : Sous-domaine par défaut ([slug].batistack.app)
            $slug = $this->extractSlugFromHost($host);

            if ($slug) {
                return Tenants::where('slug', $slug)
                    ->where('status', \App\Enums\Core\TenantStatus::Active)
                    ->first();
            }

            return null;
        });
    }

    private function extractSlugFromHost(string $host): ?string
    {
        // Format attendu: [slug].batistack.app (local: [slug].test)
        $pattern = app()->isProduction()
            ? '/^(?P<slug>[\w\-]+)\.batistack\.app$/'
            : '/^(?P<slug>[\w\-]+)\.test$/';

        if (preg_match($pattern, $host, $matches)) {
            return $matches['slug'];
        }

        return null;
    }

    public function clearCache(?string $host = null): void
    {
        if ($host) {
            Cache::forget("tenant:host:{$host}");
        } else {
            // Invalider tous les caches tenant
            Cache::tags(['tenant'])->flush();
        }
    }
}
