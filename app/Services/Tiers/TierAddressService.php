<?php

namespace App\Services\Tiers;

use App\Models\Tiers\TierAddress;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Collection;

class TierAddressService
{
    public function addAddress(Tiers $tier, array $data): TierAddress
    {
        if ($data['is_default'] ?? false) {
            $tier->addresses()->update(['is_default' => false]);
        }

        return $tier->addresses()->create([
            'type' => $data['type'],
            'name' => $data['name'] ?? null,
            'street_address' => $data['street_address'],
            'postal_code' => $data['postal_code'],
            'city' => $data['city'],
            'country' => $data['country'] ?? 'FR',
            'additional_info' => $data['additional_info'] ?? null,
            'is_default' => $data['is_default'] ?? false,
        ]);
    }

    public function updateAddress(TierAddress $address, array $data): TierAddress
    {
        if (($data['is_default'] ?? false) && ! $address->is_default) {
            $address->tiers->addresses()->update(['is_default' => false]);
        }

        $address->update([
            'type' => $data['type'] ?? $address->type,
            'name' => $data['name'] ?? $address->name,
            'street_address' => $data['street_address'] ?? $address->street_address,
            'postal_code' => $data['postal_code'] ?? $address->postal_code,
            'city' => $data['city'] ?? $address->city,
            'country' => $data['country'] ?? $address->country,
            'additional_info' => $data['additional_info'] ?? $address->additional_info,
            'is_default' => $data['is_default'] ?? $address->is_default,
        ]);

        return $address;
    }

    public function deleteAddress(TierAddress $address): bool
    {
        // Garantir qu'il y a au moins une adresse
        if ($address->tiers->addresses()->count() <= 1) {
            throw new \Exception('Un tiers doit avoir au moins une adresse.');
        }

        return $address->delete();
    }

    public function setDefaultAddress(Tiers $tier, TierAddress $address): void
    {
        $tier->addresses()->update(['is_default' => false]);
        $address->update(['is_default' => true]);
    }

    public function getDefaultAddress(Tiers $tier): ?TierAddress
    {
        return $tier->addresses()
            ->where('is_default', true)
            ->first();
    }

    public function getAddressesByType(Tiers $tier, string $type): Collection
    {
        return $tier->addresses()
            ->where('type', $type)
            ->get();
    }
}
