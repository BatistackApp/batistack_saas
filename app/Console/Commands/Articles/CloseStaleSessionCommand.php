<?php

namespace App\Console\Commands\Articles;

use App\Enums\Articles\InventorySessionStatus;
use App\Models\Articles\InventorySession;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CloseStaleSessionCommand extends Command
{
    protected $signature = 'inventory:check-stale-sessions';

    protected $description = 'Alerte sur les sessions d\'inventaire ouvertes depuis plus de 48h.';

    public function handle(): int
    {
        $staleLimit = now()->subHours(48);

        $staleSessions = InventorySession::whereIn('status', [InventorySessionStatus::Open, InventorySessionStatus::Counting])
            ->where('opened_at', '<', $staleLimit)
            ->get();

        if ($staleSessions->isEmpty()) {
            return Command::SUCCESS;
        }

        foreach ($staleSessions as $session) {
            $this->warn("La session {$session->reference} est ouverte depuis trop longtemps.");

            // Logique d'alerte : Envoyer une notification au créateur
            // $session->creator->notify(new StaleInventoryWarningNotification($session));

            Log::warning("Inventaire stagnant détecté : {$session->reference} (Dépôt: {$session->warehouse->name})");
        }

        return Command::SUCCESS;
    }
}
