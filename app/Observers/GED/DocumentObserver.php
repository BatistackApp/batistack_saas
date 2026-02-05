<?php

namespace App\Observers\GED;

use App\Models\GED\Document;
use App\Notifications\GED\QuotaWarningNotification;
use App\Services\GED\GEDService;
use App\Services\GED\QuotaService;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class DocumentObserver
{
    public function __construct(protected QuotaService $quotaService, protected GEDService $service) {}
    public function created(Document $document): void
    {
        $tenant = $document->tenant;
        $usage = $this->service->getQuotaStats()['percentage'];

        // Si on dépasse 80%, on alerte les administrateurs du Tenant
        if ($usage >= 80) {
            $admins = $tenant->users()->where('role', 'admin')->get();
            Notification::send($admins, new QuotaWarningNotification($tenant, $usage));
        }

        // Lancer le Job de génération de miniature
        \App\Jobs\GED\GenerateThumbnailJob::dispatch($document);
    }

    public function forceDeleted(Document $document): void
    {
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Supprimer aussi la miniature si elle existe, en utilisant le chemin stocké dans les métadonnées
        if (isset($document->metadata['thumbnail']) && Storage::disk('public')->exists($document->metadata['thumbnail'])) {
            Storage::disk('public')->delete($document->metadata['thumbnail']);
        }
    }
}
