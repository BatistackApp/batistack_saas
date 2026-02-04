<?php

namespace App\Services\Banque;

use App\Enums\Banque\BankSyncStatus;
use App\Enums\Banque\BankTransactionType;
use App\Models\Banque\BankAccount;
use App\Models\Banque\BankTransaction;
use DB;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Log;

class BankingSyncService
{
    private string $endpoint = 'https://api.bridgeapi.io/v3';

    /**
     * Synchronise les transactions d'un compte spécifique via la V3.
     */
    public function syncAccount(BankAccount $account): int
    {
        if (! $account->bridge_id) {
            throw new Exception("Ce compte n'est pas lié à Bridge API.");
        }

        try {
            $queryParams = [
                'account_id' => $account->bridge_id,
            ];

            // Utilisation du paramètre since pour l'incrémental (standard Bridge)
            if ($account->last_synced_at) {
                $queryParams['since'] = $account->last_synced_at->toIso8601String();
            }

            $response = Http::withHeaders($this->getHeaders())
                ->get("{$this->endpoint}/transactions", $queryParams);

            if ($response->status() === 401 || $response->status() === 403) {
                $account->update(['sync_status' => BankSyncStatus::Error]);
                throw new Exception('Authentification Bridge V3 expirée ou invalide.');
            }

            if ($response->failed()) {
                Log::error('Erreur Bridge API V3: '.$response->body());
                throw new Exception('Échec de la récupération des flux bancaires.');
            }

            $resources = $response->json('resources') ?? [];

            return $this->processBridgeResources($account, $resources);

        } catch (Exception $e) {
            Log::error("Sync Error [Account {$account->id}] (V3): ".$e->getMessage());
            throw $e;
        }
    }

    /**
     * Traite les ressources V3 et les transforme en transactions locales.
     */
    protected function processBridgeResources(BankAccount $account, array $resources): int
    {
        return DB::transaction(function () use ($account, $resources) {
            $count = 0;

            foreach ($resources as $res) {
                // Dédoublonnage via l'ID unique Bridge
                if (BankTransaction::where('external_id', $res['id'])->exists()) {
                    continue;
                }

                $amount = (float) $res['amount'];

                // En V3, la date de réservation (booking_date) reste la référence comptable
                $valueDate = isset($res['booking_date'])
                    ? Carbon::parse($res['booking_date'])
                    : Carbon::parse($res['date']);

                BankTransaction::create([
                    'tenants_id' => $account->tenants_id,
                    'bank_account_id' => $account->id,
                    'value_date' => $valueDate,
                    'label' => $res['description'] ?? 'Transaction sans libellé',
                    'amount' => $amount,
                    'type' => $amount > 0 ? BankTransactionType::Credit : BankTransactionType::Debit,
                    'external_id' => $res['id'],
                    'raw_metadata' => $res,
                    'is_reconciled' => false,
                ]);

                $account->increment('current_balance', $amount);
                $count++;
            }

            $account->update([
                'last_synced_at' => now(),
                'sync_status' => BankSyncStatus::Active,
            ]);

            return $count;
        });
    }

    /**
     * Configuration des headers pour la V3.
     */
    protected function getHeaders(): array
    {
        return [
            'Client-Id' => config('services.bridge.client_id'),
            'Client-Secret' => config('services.bridge.client_secret'),
            'Accept' => 'application/json',
            'Bridge-Version' => '2025-01-15',
        ];
    }
}
