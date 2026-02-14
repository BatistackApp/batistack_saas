<?php

namespace App\Observers\Accounting;

use App\Models\Accounting\AccountingEntry;
use App\Services\Accounting\AccountingEntryService;

class AutomatedEntryObserver
{
    public function __construct(
        private AccountingEntryService $entryService,
    ) {}

    /**
     * Après création automatique d'une écriture (depuis Commerce, Banque, etc.),
     * valider automatiquement si tous les critères sont respectés.
     */
    public function created(AccountingEntry $entry): void
    {
        // Vérifier si l'écriture provient d'une source automatisée
        if ($entry->created_from_automation) {
            try {
                $this->entryService->validate($entry);
            } catch (\Exception $e) {
                // Logger l'erreur pour révision manuelle
                \Illuminate\Support\Facades\Log::error(
                    "Erreur validation automatique écriture {$entry->reference_number}: {$e->getMessage()}"
                );
            }
        }
    }
}
