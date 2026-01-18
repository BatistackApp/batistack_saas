<?php

namespace App\Observers\Tiers;

use App\Models\Tiers\TierContact;

class TierContactObserver
{
    public function updated(TierContact $contact): void
    {
        // Si marquée comme primary, retirer le statut des autres
        if ($contact->isDirty('is_primary') && $contact->is_primary) {
            $contact->tiers->contacts()
                ->where('id', '!=', $contact->id)
                ->update(['is_primary' => false]);
        }
    }

    public function deleting(TierContact $contact): void
    {
        // Si c'était le contact primaire, en assigner un autre
        if ($contact->is_primary && $contact->tiers->contacts()->count() > 1) {
            $contact->tiers->contacts()
                ->where('id', '!=', $contact->id)
                ->first()?->update(['is_primary' => true]);
        }
    }
}
