<?php

namespace App\Observers\Articles;

use App\Enums\Articles\InventorySessionStatus;
use App\Models\Articles\InventorySession;
use Log;

class InventorySessionObserver
{
    public function creating(InventorySession $session): void
    {
        if (empty($session->reference)) {
            $session->reference = 'INV-'.now()->format('Ymd').'-'.strtoupper(substr(uniqid(), -4));
        }
    }

    public function updated(InventorySession $session): void
    {
        // Si le statut a changé, on trace l'événement
        if ($session->wasChanged('status')) {
            Log::info("Changement de statut Inventaire [{$session->reference}] : ".
                $session->getOriginal('status')->value.' -> '.$session->status->value);
        }

        // Si la session est validée ou annulée, on s'assure que le gel est bien levé (sécurité secondaire)
        if (in_array($session->status, [InventorySessionStatus::Validated, InventorySessionStatus::Cancelled])) {
            // Ici on pourrait déclencher un événement système pour notifier le module Planning
        }
    }
}
