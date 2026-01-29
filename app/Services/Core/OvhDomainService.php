<?php

namespace App\Services\Core;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class OvhDomainService
{
    private Client $client;
    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => config('services.ovh.api_url'),
            'headers' => [
                'X-Ovh-Application' => config('services.ovh.app_key'),
                'X-Ovh-Consumer' => config('services.ovh.consumer_key'),
                'Authorization' => 'Bearer ' . config('services.ovh.token'),
            ]
        ]);
    }

    public function createSubdomain(string $slug, int $tenantId): bool
    {
        if (! app()->isProduction()) {
            Log::debug("OVH: Simulated subdomain creation for {$slug}.batistack.app");
            return true;
        }

        try {
            $domain = config('services.ovh.base_domain'); // batistack.app

            $this->client->post("/domain/{$domain}/records", [
                'json' => [
                    'fieldType' => 'CNAME',
                    'subDomain' => $slug,
                    'target' => config('app.url'), // Pointe vers le serveur principal
                    'ttl' => 3600,
                ],
            ]);

            Log::info("OVH: Subdomain {$slug}.batistack.app created for tenant {$tenantId}");

            return true;
        } catch (\Exception $e) {
            Log::error("OVH: Failed to create subdomain for {$slug}", [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
            ]);

            throw $e;
        } catch (GuzzleException $e) {
            Log::error("OVH: Failed to create subdomain for {$slug}", [
                'error' => $e->getMessage(),
                'tenant_id' => $tenantId,
            ]);

            throw $e;
        }
    }

    public function deleteSubdomain(string $slug): bool
    {
        if (! app()->isProduction()) {
            Log::debug("OVH: Simulated subdomain deletion for {$slug}.batistack.app");
            return true;
        }

        try {
            $domain = config('services.ovh.base_domain');

            $this->client->delete("/domain/{$domain}/records", [
                'json' => [
                    'fieldType' => 'CNAME',
                    'subDomain' => $slug,
                ],
            ]);

            Log::info("OVH: Subdomain {$slug}.batistack.app deleted");

            return true;
        } catch (\Exception $e) {
            Log::error("OVH: Failed to delete subdomain {$slug}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        } catch (GuzzleException $e) {
            Log::error("OVH: Failed to delete subdomain {$slug}", [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }
}
