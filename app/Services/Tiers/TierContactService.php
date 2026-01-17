<?php

namespace App\Services\Tiers;

use App\Models\Tiers\TierContact;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Eloquent\Collection;

class TierContactService
{
    public function addContact(Tiers $tier, array $data): TierContact
    {
        if ($data['is_primary'] ?? false) {
            $tier->contacts()->update(['is_primary' => false]);
        }

        return $tier->contacts()->create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'position' => $data['position'] ?? null,
            'notes' => $data['notes'] ?? null,
            'is_primary' => $data['is_primary'] ?? false,
        ]);
    }

    public function updateContact(TierContact $contact, array $data): TierContact
    {
        if (($data['is_primary'] ?? false) && ! $contact->is_primary) {
            $contact->tiers->contacts()->update(['is_primary' => false]);
        }

        $contact->update([
            'first_name' => $data['first_name'] ?? $contact->first_name,
            'last_name' => $data['last_name'] ?? $contact->last_name,
            'email' => $data['email'] ?? $contact->email,
            'phone' => $data['phone'] ?? $contact->phone,
            'position' => $data['position'] ?? $contact->position,
            'notes' => $data['notes'] ?? $contact->notes,
            'is_primary' => $data['is_primary'] ?? $contact->is_primary,
        ]);

        return $contact;
    }

    public function deleteContact(TierContact $contact): bool
    {
        return $contact->delete();
    }

    public function setPrimaryContact(Tiers $tier, TierContact $contact): void
    {
        $tier->contacts()->update(['is_primary' => false]);
        $contact->update(['is_primary' => true]);
    }

    public function getPrimaryContact(Tiers $tier): ?TierContact
    {
        return $tier->contacts()
            ->where('is_primary', true)
            ->first();
    }

    public function getContacts(Tiers $tier): Collection
    {
        return $tier->contacts()
            ->orderBy('is_primary', 'desc')
            ->get();
    }
}
