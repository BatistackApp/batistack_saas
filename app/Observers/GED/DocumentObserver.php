<?php

namespace App\Observers\GED;

use App\Jobs\GED\GenerateThumbnailJob;
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

        // 1. Vérification du quota et notification si > 80%
        $usageStats = $this->quotaService->getUsageStats($tenant);
        if ($usageStats['percentage'] >= 80) {
            $admins = $tenant->users()->where('role', 'admin')->get();
            Notification::send($admins, new QuotaWarningNotification($tenant, $usageStats['percentage']));
        }

        // 2. Génération de la miniature en arrière-plan
        GenerateThumbnailJob::dispatch($document);
    }

    public function forceDeleted(Document $document): void
    {
        if ($document->file_path && Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // 2. Suppression de la miniature (stockée dans metadata)
        $thumbnail = $document->metadata['thumbnail'] ?? null;
        if ($thumbnail && Storage::disk('public')->exists($thumbnail)) {
            Storage::disk('public')->delete($thumbnail);
        }

        // 3. Libération du quota
        $this->quotaService->decrementUsedStorage($document->tenant, $document->size);
    }
}
