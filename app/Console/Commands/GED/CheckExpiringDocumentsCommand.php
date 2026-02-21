<?php

namespace App\Console\Commands\GED;

use App\Enums\GED\DocumentStatus;
use App\Models\GED\Document;
use App\Notifications\GED\DocumentExpiringNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class CheckExpiringDocumentsCommand extends Command
{
    protected $signature = 'ged:check-expirations';

    protected $description = 'Vérifie les documents arrivant à expiration et notifie les responsables.';

    public function handle(): void
    {
        /**
         * Logique de seuils :
         * 30 jours : Première alerte
         * 15 jours : Rappel intermédiaire
         * 7 jours  : Alerte urgente
         * < 3 jours : Alerte quotidienne jusqu'à expiration
         */
        $thresholds = [
            ['days' => 30, 'cooldown' => 14], // Alerte à J-30, puis silence pendant 14j
            ['days' => 15, 'cooldown' => 7],  // Alerte à J-15, puis silence pendant 7j
            ['days' => 7,  'cooldown' => 4],  // Alerte à J-7, puis silence pendant 4j
            ['days' => 3,  'cooldown' => 0],  // Alerte quotidienne les 3 derniers jours
        ];

        foreach ($thresholds as $threshold) {
            $days = $threshold['days'];
            $cooldown = $threshold['cooldown'];

            // On cible les documents expirant dans cette plage
            $targetDate = now()->addDays($days)->toDateString();

            Document::where('expires_at', '<=', $targetDate)
                ->where('expires_at', '>', now()->toDateString()) // Pas encore expiré
                ->where('status', DocumentStatus::Validated->value) // Uniquement les documents actifs
                ->where(function ($query) use ($cooldown) {
                    $query->whereNull('last_alert_sent_at')
                        ->orWhere('last_alert_sent_at', '<=', now()->subDays($cooldown)->startOfDay());
                })
                ->with(['uploader', 'tenant'])
                ->chunk(100, function ($documents) use ($days) {
                    foreach ($documents as $document) {
                        $this->sendNotification($document, $days);
                    }
                });
        }

        $this->info('Vérification des expirations terminée.');
    }

    /**
     * Envoie la notification et met à jour le timestamp
     */
    protected function sendNotification(Document $document, int $daysRemaining): void
    {
        // Construction de la liste des destinataires (Uploadeur + Admins du Tenant)
        $recipients = collect();

        if ($document->uploader) {
            $recipients->push($document->uploader);
        }

        $admins = $document->tenant->users()
            ->where('role', 'admin')
            ->get();

        $recipients = $recipients->concat($admins)
            ->filter()
            ->unique('id');

        if ($recipients->isNotEmpty()) {
            Notification::send($recipients, new DocumentExpiringNotification($document, $daysRemaining));

            // On marque le document pour éviter le spam lors du prochain passage
            $document->update([
                'last_alert_sent_at' => now(),
            ]);

            $this->line("Alerte envoyée pour : {$document->name} (Expire dans {$daysRemaining}j environ)");
        }
    }
}
