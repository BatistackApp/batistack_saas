<?php

namespace App\Jobs\Banque;

use App\Enums\Banque\BankSyncStatus;
use App\Models\Banque\BankAccount;
use App\Notifications\Banque\BankConnectionErrorNotification;
use App\Services\Banque\BankingSyncService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SyncBankTransactionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Nombre de tentatives avant échec (Bridge peut avoir des micro-coupures).
     */
    public int $tries = 3;

    /**
     * Temps d'attente entre les tentatives (en secondes).
     */
    public array $backoff = [60, 300, 600];

    public function __construct(protected BankAccount $account) {}

    public function handle(BankingSyncService $syncService): void
    {
        Log::info("Début de synchro V3 pour le compte: {$this->account->name} (Tenant: {$this->account->tenants_id})");

        try {
            $count = $syncService->syncAccount($this->account);

            Log::info("Synchro V3 terminée : {$count} nouvelles transactions importées.");
        } catch (\Exception $e) {
            // ANALYSE DE L'ERREUR (Spécifique V3)
            // On cherche les mots clés définis dans le BankingSyncService V3
            $errorMessage = strtolower($e->getMessage());

            if (str_contains($errorMessage, 'expirée') || str_contains($errorMessage, 'invalide') || str_contains($errorMessage, '401')) {

                $this->account->update(['sync_status' => BankSyncStatus::Error]);

                // On notifie le propriétaire du compte pour qu'il renouvelle son consentement
                if ($this->account->tenant && $this->account->tenant->owner) {
                    $this->account->tenant->owner->notify(
                        new BankConnectionErrorNotification($this->account)
                    );
                }

                Log::warning("Synchro stoppée : Consentement bancaire expiré pour le compte {$this->account->id}.");

                return;
            }

            // Pour les autres erreurs (réseau, API Bridge down), on throw pour laisser Laravel gérer le retry
            throw $e;
        }
    }
}
