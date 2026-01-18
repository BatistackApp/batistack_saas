<?php

use App\Models\Core\AuditLog;
use App\Models\Tiers\TierAddress;
use App\Models\Tiers\TierContact;
use App\Models\Tiers\Tiers;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates audit log on tier creation', function () {
    $tier = Tiers::factory()->create();

    expect(AuditLog::where('auditable_id', $tier->id)->exists())->toBeTrue()
        ->and(AuditLog::where('auditable_id', $tier->id)->first()->action)->toBe('created');
});

it('creates audit log on tier update', function () {
    $tier = Tiers::factory()->create();
    AuditLog::where('auditable_id', $tier->id)->delete();

    $tier->update(['name' => 'Updated Name']);

    expect(AuditLog::where('auditable_id', $tier->id)->first()->action)->toBe('updated');
});

it('first address becomes default', function () {
    $tier = Tiers::factory()->create();

    $address = TierAddress::factory()->create(['tiers_id' => $tier->id, 'is_default' => false]);

    expect($address->refresh()->is_default)->toBeTrue();
});

it('only one default address per tier', function () {
    $tier = Tiers::factory()->create();
    $address1 = TierAddress::factory()->create(['tiers_id' => $tier->id, 'is_default' => true]);

    $address2 = TierAddress::factory()->create(['tiers_id' => $tier->id, 'is_default' => false]);
    $address2->update(['is_default' => true]);

    expect($address1->refresh()->is_default)->toBeFalse()
        ->and($address2->refresh()->is_default)->toBeTrue();
});

it('only one primary contact per tier', function () {
    $tier = Tiers::factory()->create();
    $contact1 = TierContact::factory()->create(['tiers_id' => $tier->id, 'is_primary' => true]);

    $contact2 = TierContact::factory()->create(['tiers_id' => $tier->id, 'is_primary' => false]);
    $contact2->update(['is_primary' => true]);

    expect($contact1->refresh()->is_primary)->toBeFalse()
        ->and($contact2->refresh()->is_primary)->toBeTrue();
});
