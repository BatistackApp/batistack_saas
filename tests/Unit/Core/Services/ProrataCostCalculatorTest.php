<?php

use App\Enums\Core\BillingCycle;
use App\Models\Core\Tenants;
use App\Services\Core\ProrataCostCalculator;

describe('ProrataCostCalculator', function () {
    beforeEach(function () {
        $this->calculator = app(ProrataCostCalculator::class);
    });

    it('calcule correctement le crédit pro-rata pour upgrade après 15 jours', function () {
        $tenant = Tenants::factory()->create();

        $subscription = $tenant->subscriptions()->create([
            'stripe_id' => 'sub_upgrade_test',
            'stripe_status' => 'active',
            'stripe_price' => 'price_monthly_468',
            'quantity' => 1,
            'created_at' => now()->subDays(15),
            'user_id' => 1,
            'type' => Tenants::class,
        ]);

        $adjustment = $this->calculator->calculateUpgradeAdjustment(
            tenant: $tenant,
            currentCycle: BillingCycle::Monthly,
            currentPrice: 600,
            newCycle: BillingCycle::Monthly,
            newPrice: 430,
        );

        expect($adjustment)->toBeGreaterThan(0);
    });

    it('retourne 0 si pas de souscription active', function () {
        $tenant = Tenants::factory()->create();

        $adjustment = $this->calculator->calculateUpgradeAdjustment(
            tenant: $tenant,
            currentCycle: BillingCycle::Monthly,
            currentPrice: 0,
            newCycle: BillingCycle::Monthly,
            newPrice: 468.00,
        );

        expect($adjustment)->toEqual(0);
    });

    it('gère les downgrade (montant négatif)', function () {
        $tenant = Tenants::factory()->create();

        $subscription = $tenant->subscriptions()->create([
            'stripe_id' => 'sub_downgrade',
            'stripe_status' => 'active',
            'stripe_price' => 'price_yearly_5928',
            'quantity' => 1,
            'created_at' => now()->subDays(100),
            'user_id' => 1,
            'type' => Tenants::class,
        ]);

        $adjustment = $this->calculator->calculateUpgradeAdjustment(
            tenant: $tenant,
            currentCycle: BillingCycle::Yearly,
            currentPrice: 5928.00,
            newCycle: BillingCycle::Yearly,
            newPrice: 6000,
        );

        expect($adjustment)->toBeLessThan(0);
    });
});
