<?php

use App\Models\Core\Module;
use App\Models\Core\Tenant;
use App\Models\Core\TenantBillingInfo;
use App\Models\Core\TenantModule;
use App\Models\User;
use Tests\TestCase;

describe('Factories - Basic Creation', function () {
    test('UserFactory creates a valid user', function () {
        $user = User::factory()->create();

        expect($user)->toBeInstanceOf(User::class)
            ->and($user->id)->not->toBeNull()
            ->and($user->name)->not->toBeNull()
            ->and($user->email)->not->toBeNull()
            ->and($user->password)->not->toBeNull()
            ->and(strlen($user->remember_token))->toBe(10);
    });

    test('UserFactory creates unique emails', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        expect($user1->email)->not->toEqual($user2->email);
    });

    test('TenantFactory creates a valid tenant', function () {
        $tenant = Tenant::factory()->create();

        expect($tenant)->toBeInstanceOf(Tenant::class)
            ->and($tenant->id)->not->toBeNull()
            ->and($tenant->name)->not->toBeNull()
            ->and($tenant->slug)->not->toBeNull();
    });

    test('TenantBillingInfoFactory creates valid billing info', function () {
        $billingInfo = TenantBillingInfo::factory()->create();

        expect($billingInfo)->toBeInstanceOf(TenantBillingInfo::class)
            ->and($billingInfo->company_name)->not->toBeNull()
            ->and($billingInfo->billing_email)->not->toBeNull()
            ->and($billingInfo->billing_address)->not->toBeNull()
            ->and($billingInfo->postal_code)->not->toBeNull()
            ->and($billingInfo->city)->not->toBeNull()
            ->and($billingInfo->country)->not->toBeNull()
            ->and($billingInfo->vat_number)->not->toBeNull()
            ->and($billingInfo->phone)->not->toBeNull()
            ->and($billingInfo->tenant_id)->not->toBeNull();
    });

    test('TenantBillingInfoFactory generates realistic VAT numbers', function () {
        $billingInfo = TenantBillingInfo::factory()->create();

        expect($billingInfo->vat_number)->toMatch('/^[A-Z]{2,3}\d+$/');
    });

    test('ModuleFactory creates a valid module', function () {
        $module = Module::factory()->create();

        expect($module)->toBeInstanceOf(Module::class)
            ->and($module->name)->not->toBeNull()
            ->and($module->slug)->not->toBeNull()
            ->and($module->description)->not->toBeNull()
            ->and($module->is_active)->toBeTrue();
    });

    test('ModuleFactory creates modules with realistic data', function () {
        $modules = Module::factory()->count(5)->create();

        $moduleNames = $modules->pluck('name')->unique();
        expect($moduleNames->count())->toBeGreaterThan(0);
    });

    test('TenantModuleFactory creates a valid tenant module', function () {
        $tenantModule = TenantModule::factory()->create();

        expect($tenantModule)->toBeInstanceOf(TenantModule::class)
            ->and($tenantModule->billing_period)->toBeInstanceOf(\App\Enums\Core\BillingPeriod::class)
            ->and($tenantModule->is_active)->toBeTrue()
            ->and($tenantModule->stripe_subscription_id)->toMatch('/^sub_/')
            ->and($tenantModule->subscribed_at)->not->toBeNull()
            ->and($tenantModule->expires_at)->not->toBeNull()
            ->and($tenantModule->tenant_id)->not->toBeNull()
            ->and($tenantModule->module_id)->not->toBeNull();
    });

    test('TenantModuleFactory creates realistic Stripe IDs', function () {
        $tenantModule = TenantModule::factory()->create();

        expect($tenantModule->stripe_subscription_id)
            ->toMatch('/^sub_[a-f0-9]{32}$/');
    });

    test('TenantModuleFactory creates realistic dates', function () {
        $tenantModule = TenantModule::factory()->create();

        expect($tenantModule->subscribed_at)->toBeLessThanOrEqual(now())
            ->and($tenantModule->expires_at)->toBeGreaterThan(now());
    });
});
