<?php

namespace App\Console\Commands\GED;

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
        // On récupère les documents qui expirent dans exactement 30, 15 ou 7 jours
        $intervals = [30, 15, 7];

        foreach ($intervals as $days) {
            $targetDate = now()->addDays($days)->toDateString();

            Document::where('expires_at', $targetDate)
                ->where('is_valid', true)
                ->with(['uploader', 'tenant'])
                ->chunk(100, function ($documents) use ($days) {
                    foreach ($documents as $document) {
                        // Notifier l'uploadeur et les admins du tenant
                        $recipients = collect([$document->uploader])
                            ->concat($document->tenant->users()->where('role', 'admin')->get())
                            ->filter()
                            ->unique('id');

                        Notification::send($recipients, new DocumentExpiringNotification($document, $days));
                    }
                });
        }

        $this->info('Vérification des expirations terminée.');

    }
}
