<?php

namespace Tests\Unit\Services\Tiers;

use App\Models\Tiers\Tiers;
use App\Services\Tiers\TierCommercialTermsService;

beforeEach(function () {
    $this->termsService = new TierCommercialTermsService();
});

it('can update commercial terms', function () {
    $tier = Tiers::factory()->create([
        'discount_percentage' => 5,
        'payment_delay_days' => 30,
    ]);

    $updated = $this->termsService->updateCommercialTerms($tier, [
        'discount_percentage' => 10,
        'payment_delay_days' => 60,
    ]);

    expect($updated->discount_percentage)->toBe('10.00')
        ->and($updated->payment_delay_days)->toBe(60);
});

it('rejects invalid discount percentage', function () {
    $tier = Tiers::factory()->create();

    expect(fn () => $this->termsService->updateCommercialTerms($tier, [
        'discount_percentage' => 150,
    ]))->toThrow(\InvalidArgumentException::class);
});

it('rejects negative payment delay', function () {
    $tier = Tiers::factory()->create();

    expect(fn () => $this->termsService->updateCommercialTerms($tier, [
        'payment_delay_days' => -10,
    ]))->toThrow(\InvalidArgumentException::class);
});

it('can get commercial terms', function () {
    $tier = Tiers::factory()->create([
        'discount_percentage' => 7.5,
        'payment_delay_days' => 45,
        'iban' => 'FR1420041010050500013M02606',
        'vat_number' => 'FR12345678901',
    ]);

    $terms = $this->termsService->getCommercialTerms($tier);

    expect($terms)->toMatchArray([
        'discount_percentage' => 7.5,
        'payment_delay_days' => 45,
        'iban' => 'FR1420041010050500013M02606',
        'vat_number' => 'FR12345678901',
    ]);
});
