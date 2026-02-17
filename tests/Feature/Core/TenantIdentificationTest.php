<?php

use App\Models\Core\Tenants;
use App\Services\Core\TenantIdentificationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('identifies tenant by custom domain', function () {
    $tenant = Tenants::factory()->create([
        'domain' => 'custom.example.com',
        'slug' => 'custom-tenant',
        'status' => \App\Enums\Core\TenantStatus::Active->value,
    ]);

    // Créer une route de test qui retourne le tenant identifié
    Route::get('/test-tenant-identify', function () {
        $service = app(TenantIdentificationService::class);
        $identified = $service->identifyFromRequest();

        return response()->json(['tenant_id' => $identified?->id]);
    });

    $response = $this->get('http://custom.example.com/test-tenant-identify');

    $response->assertOk()
        ->assertJson(['tenant_id' => $tenant->id]);
});

it('identifies tenant by slug subdomain', function () {
    $tenant = Tenants::factory()->create([
        'slug' => 'my-tenant',
        'domain' => null,
        'status' => \App\Enums\Core\TenantStatus::Active->value,
    ]);

    Route::get('/test-tenant-identify', function () {
        $service = app(TenantIdentificationService::class);
        $identified = $service->identifyFromRequest();

        return response()->json(['tenant_id' => $identified?->id]);
    });

    $response = $this->get('http://my-tenant.test/test-tenant-identify');

    $response->assertOk()
        ->assertJson(['tenant_id' => $tenant->id]);
});

it('returns null for unknown host', function () {
    Route::get('/test-tenant-identify', function () {
        $service = app(TenantIdentificationService::class);
        $identified = $service->identifyFromRequest();

        return response()->json(['tenant_id' => $identified?->id]);
    });

    $response = $this->get('http://unknown.test/test-tenant-identify');

    $response->assertOk()
        ->assertJson(['tenant_id' => null]);
});

it('caches tenant identification', function () {
    $tenant = Tenants::factory()->create([
        'slug' => 'cached-tenant',
        'status' => \App\Enums\Core\TenantStatus::Active->value,
    ]);

    Route::get('/test-tenant-identify', function () {
        $service = app(TenantIdentificationService::class);

        return response()->json(['tenant_id' => $service->identifyFromRequest()?->id]);
    });

    $this->get('http://cached-tenant.test/test-tenant-identify')->assertJson(['tenant_id' => $tenant->id]);

    $this->assertTrue(\Illuminate\Support\Facades\Cache::has(
        'tenant:host:cached-tenant.test'
    ));
});
