<?php

namespace App\Observers\Tiers;

use App\Models\Tiers\TierAddress;

class TierAddressObserver
{
    public function created(TierAddress $address): void
    {
        if ($address->tiers->addresses()->count() === 1) {
            $address->updateQuietly(['is_default' => true]);
        }
    }

    public function updated(TierAddress $address): void
    {
        // Si marquée comme default, retirer le statut des autres
        if ($address->isDirty('is_default') && $address->is_default) {
            $address->tiers->addresses()
                ->where('id', '!=', $address->id)
                ->update(['is_default' => false]);
        }
    }

    public function deleting(TierAddress $address): void
    {
        // Empêcher la suppression si c'est la seule adresse
        if ($address->tiers->addresses()->count() === 1) {
            throw new \Exception('Un tiers doit avoir au moins une adresse.');
        }

        // Si c'était l'adresse default, en assigner une autre
        if ($address->is_default) {
            $address->tiers->addresses()
                ->where('id', '!=', $address->id)
                ->first()?->update(['is_default' => true]);
        }
    }
}
