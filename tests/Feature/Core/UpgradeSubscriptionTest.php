<?php

use App\Enums\Core\TenantStatus;
use App\Models\Core\Tenants;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('UpgradeSubscription', function () {
    it('upgrade un abonnement après 15 jours et applique le crédit pro-rata', function () {
        $tenant = Tenants::factory()
            ->create(['status' => TenantStatus::Active->value]);

        $subscription = $tenant->subscriptions()->create([
            'name' => 'default',
            'stripe_id' => 'sub_upgrade_15d',
            'stripe_status' => 'active',
            'stripe_price' => 'price_monthly_468',
            'quantity' => 1,
            'created_at' => now()->subDays(15),
        ]);

        $response = $this->postJson('/api/billing/subscriptions/upgrade', [
            'tenant_id' => $tenant->id,
            'new_plan_id' => 'price_monthly_600',
        ]);

        $response->assertSuccessful();
    });

    it('refuse l\'upgrade si l\'abonnement est expiré', function () {
        $tenant = Tenants::factory()
            ->create(['status' => TenantStatus::Active->value]);

        $subscription = $tenant->subscriptions()->create([
            'name' => 'default',
            'stripe_id' => 'sub_expired',
            'stripe_status' => 'active',
            'stripe_price' => 'price_monthly_468',
            'quantity' => 1,
            'ends_at' => now()->subDays(5),
        ]);

        $response = $this->postJson('/api/billing/subscriptions/upgrade', [
            'tenant_id' => $tenant->id,
            'new_plan_id' => 'price_monthly_600',
        ]);

        $response->assertForbidden();
    });
});
