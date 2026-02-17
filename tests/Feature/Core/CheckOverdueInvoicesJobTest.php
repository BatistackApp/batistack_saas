<?php

use App\Enums\Core\TenantStatus;
use App\Jobs\Core\CheckOverdueInvoicesJob;
use App\Models\Core\Tenants;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

describe('CheckOverdueInvoicesJob', function () {
    it('suspend un tenant avec facture impayée depuis 30+ jours', function () {
        Notification::fake();
        $tenant = Tenants::factory()
            ->create(['status' => TenantStatus::Active->value]);

        $subscription = $tenant->subscriptions()->create([
            'stripe_id' => 'sub_overdue_30',
            'stripe_status' => 'past_due',
            'stripe_price' => 'price_monthly_468',
            'quantity' => 1,
            'created_at' => now()->subDays(35),
            'type' => Tenants::class,
            'user_id' => 1,
        ]);

        $job = new CheckOverdueInvoicesJob;
        $job->handle();

        $tenant->refresh();

        expect($tenant->status)->toBe(TenantStatus::Suspended);
    });

    it('ignore les tenants déjà suspendus', function () {
        $tenant = Tenants::factory()
            ->create(['status' => TenantStatus::Suspended->value]);

        $subscription = $tenant->subscriptions()->create([
            'stripe_id' => 'sub_suspended',
            'stripe_status' => 'past_due',
            'stripe_price' => 'price_monthly_468',
            'quantity' => 1,
            'created_at' => now()->subDays(35),
            'type' => Tenants::class,
        ]);

        $job = new CheckOverdueInvoicesJob;
        $job->handle();

        $tenant->refresh();

        expect($tenant->status)->toBe(TenantStatus::Suspended);
    });

    it('n\'affecte pas les factures payées < 30 jours', function () {
        Notification::fake();
        $tenant = Tenants::factory()
            ->create(['status' => TenantStatus::Active->value]);

        $subscription = $tenant->subscriptions()->create([
            'stripe_id' => 'sub_recent_overdue',
            'stripe_status' => 'past_due',
            'stripe_price' => 'price_monthly_468',
            'quantity' => 1,
            'created_at' => now()->subDays(15),
            'type' => Tenants::class,
        ]);

        $job = new CheckOverdueInvoicesJob;
        $job->handle();

        $tenant->refresh();

        expect($tenant->status)->toBe(TenantStatus::Active->value);
    });
});
