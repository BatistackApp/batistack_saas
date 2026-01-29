<?php

use App\Enums\Core\BillingCycle;
use App\Models\Core\Tenants;
use App\Services\Core\SubscriptionService;

describe('SubscriptionService', function () {
    beforeEach(function () {
        $this->service = app(SubscriptionService::class);
        $this->tenant = Tenants::factory()->create();
        $this->subscription = $this->tenant->subscriptions()->create([
            'stripe_id' => 'sub_active',
            'stripe_status' => 'active',
            'stripe_price' => 'price_monthly_468',
            'quantity' => 1,
            'created_at' => now(),
            'ends_at' => null,
            'user_id' => 1,
            'type' => Tenants::class,
        ]);
    });

    it('identifie une souscription active', function () {
        $isActive = $this->service->isSubscriptionActive($this->tenant);

        expect($isActive)->toBeTrue();
    });

    it('détecte une souscription expirée', function () {
        $tenant = Tenants::factory()->create();

        $subscription = $tenant->subscriptions()->create([
            'stripe_id' => 'sub_expired',
            'stripe_status' => 'active',
            'stripe_price' => 'price_monthly_468',
            'quantity' => 1,
            'created_at' => now()->subDays(30),
            'ends_at' => now()->subDay(),
            'user_id' => 1,
            'type' => Tenants::class,
        ]);

        $isExpired = $this->service->isSubscriptionExpired($tenant);

        expect($isExpired)->toBeTrue();
    });

    it('calcule le coût annuel correctement', function () {
        $yearlyPrice = 494;

        $cost = $this->service->getSubscriptionCost(
            BillingCycle::Yearly,
            $yearlyPrice
        );

        expect($cost)->toEqual(5928);
    });

    it('calcule le coût mensuel x 12 pour accès annuel', function () {
        $monthlyPrice = 468.99;

        $annualCost = $this->service->getSubscriptionCost(
            BillingCycle::Monthly,
            $monthlyPrice) * 12;

        expect($annualCost)->toBeGreaterThan(5000);
    });
});
