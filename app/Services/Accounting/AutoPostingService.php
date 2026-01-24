<?php

namespace App\Services\Accounting;

use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use Illuminate\Support\Collection;

class AutoPostingService
{
    public function __construct(private EntryRecorderService $entryRecorder) {}

    /**
     * Crée et poste automatiquement une écriture
     */
    public function recordAndPost(
        Tenant $tenant,
        AccountingJournal $journal,
        string $description,
        Collection $lines,
        ?\DateTime $postedAt = null,
        ?string $sourceType = null,
        ?int $sourceId = null,
    ): AccountingEntry {
        $entry = $this->entryRecorder->record(
            $tenant,
            $journal,
            $description,
            $lines,
            $postedAt,
            $sourceType,
            $sourceId
        );

        return $this->entryRecorder->post($entry);
    }
}
