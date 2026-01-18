<?php

namespace Tests\Unit\Services\Tiers;

use App\Models\Tiers\TierContact;
use App\Models\Tiers\Tiers;
use App\Services\Tiers\TierContactService;

beforeEach(function () {
    $this->contactService = new TierContactService;
});

it('can add contact to tier', function () {
    $tier = Tiers::factory()->create();

    $contact = $this->contactService->addContact($tier, [
        'first_name' => 'Jean',
        'last_name' => 'Dupont',
        'email' => 'jean@example.com',
        'position' => 'Manager',
    ]);

    expect($contact)->toBeInstanceOf(TierContact::class)
        ->and($contact->tiers_id)->toBe($tier->id)
        ->and($contact->first_name)->toBe('Jean');
});

it('can set primary contact', function () {
    $tier = Tiers::factory()->create();
    $contact1 = TierContact::factory()->create(['tiers_id' => $tier->id, 'is_primary' => true]);
    $contact2 = TierContact::factory()->create(['tiers_id' => $tier->id, 'is_primary' => false]);

    $this->contactService->setPrimaryContact($tier, $contact2);

    expect($contact2->refresh()->is_primary)->toBeTrue()
        ->and($contact1->refresh()->is_primary)->toBeFalse();
});

it('can get primary contact', function () {
    $tier = Tiers::factory()->create();
    TierContact::factory()->create(['tiers_id' => $tier->id, 'is_primary' => false]);
    $primaryContact = TierContact::factory()->create(['tiers_id' => $tier->id, 'is_primary' => true]);

    $primary = $this->contactService->getPrimaryContact($tier);

    expect($primary->id)->toBe($primaryContact->id);
});

it('can update contact', function () {
    $contact = TierContact::factory()->create(['first_name' => 'Jean']);

    $updated = $this->contactService->updateContact($contact, ['first_name' => 'Jacques']);

    expect($updated->first_name)->toBe('Jacques');
});

it('can delete contact', function () {
    $contact = TierContact::factory()->create();

    $deleted = $this->contactService->deleteContact($contact);

    expect($deleted)->toBeTrue();
});
