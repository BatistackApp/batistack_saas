<?php

use App\Models\Core\Tenants;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('StripeWebhook', function () {
    it('traite un webhook de souscription mise à jour', function () {
        $tenant = Tenants::factory()->create();

        $response = $this->postJson('/stripe/webhook', [
            'type' => 'customer.subscription.updated',
            'data' => [
                'object' => [
                    'id' => 'sub_test_webhook',
                    'status' => 'active',
                    'customer' => $tenant->stripe_id,
                ], ],
        ], [
            'Stripe-Signature' => 'valid_signature',
        ]);

        $response->assertSuccessful();
    });

    it('rejette un webhook invalide', function () {
        $response = $this->postJson('/stripe/webhook', [
            'type' => 'customer.subscription.updated',
        ]);

        $response->assertUnauthorized();
    });
});
