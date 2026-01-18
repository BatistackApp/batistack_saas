<?php

namespace Tests\Unit\Services\Tiers;

use App\Models\Tiers\TierAddress;
use App\Models\Tiers\Tiers;
use App\Services\Tiers\TierAddressService;

beforeEach(function () {
    $this->addressService = new TierAddressService;
});

it('can add address to tier', function () {
    $tier = Tiers::factory()->create();

    $address = $this->addressService->addAddress($tier, [
        'type' => 'facturation',
        'street_address' => '123 Rue de Paris',
        'postal_code' => '75001',
        'city' => 'Paris',
    ]);

    expect($address)->toBeInstanceOf(TierAddress::class)
        ->and($address->tiers_id)->toBe($tier->id)
        ->and($address->street_address)->toBe('123 Rue de Paris');
});

it('can set default address', function () {
    $tier = Tiers::factory()->create();
    $address1 = TierAddress::factory()->create(['tiers_id' => $tier->id, 'is_default' => true]);
    $address2 = TierAddress::factory()->create(['tiers_id' => $tier->id, 'is_default' => false]);

    $this->addressService->setDefaultAddress($tier, $address2);

    expect($address2->refresh()->is_default)->toBeTrue()
        ->and($address1->refresh()->is_default)->toBeFalse();
});

it('cannot delete last address', function () {
    $tier = Tiers::factory()->create();
    $address = TierAddress::factory()->create(['tiers_id' => $tier->id]);

    expect(fn () => $this->addressService->deleteAddress($address))
        ->toThrow(\Exception::class, 'Un tiers doit avoir au moins une adresse.');
});

it('can delete address if not last', function () {
    $tier = Tiers::factory()->create();
    TierAddress::factory()->create(['tiers_id' => $tier->id]);
    $addressToDelete = TierAddress::factory()->create(['tiers_id' => $tier->id]);

    $deleted = $this->addressService->deleteAddress($addressToDelete);

    expect($deleted)->toBeTrue()
        ->and(TierAddress::find($addressToDelete->id))->toBeNull();
});

it('can get addresses by type', function () {
    $tier = Tiers::factory()->create();
    TierAddress::factory()->create(['tiers_id' => $tier->id, 'type' => 'facturation']);
    TierAddress::factory()->create(['tiers_id' => $tier->id, 'type' => 'livraison']);
    TierAddress::factory()->create(['tiers_id' => $tier->id, 'type' => 'facturation']);

    $billingAddresses = $this->addressService->getAddressesByType($tier, 'facturation');

    expect($billingAddresses)->toHaveCount(2)
        ->and($billingAddresses->every(fn ($a) => $a->type === 'facturation'))->toBeTrue();
});
