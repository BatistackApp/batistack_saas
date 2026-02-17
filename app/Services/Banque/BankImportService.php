<?php

namespace App\Services\Banque;

use App\Enums\Banque\BankTransactionType;
use App\Models\Banque\BankAccount;
use App\Models\Banque\BankTransaction;
use Carbon\Carbon;
use DB;

/**
 * Service pour l'injection manuelle et l'import CSV (Issue #32).
 */
class BankImportService
{
    /**
     * Importe des lignes depuis un tableau de données (issu d'un CSV).
     */
    public function importFromArray(BankAccount $account, array $rows): int
    {
        return DB::transaction(function () use ($account, $rows) {
            $count = 0;
            $totalAmountAdded = '0'; // Initialiser avec une chaîne pour bc functions

            foreach ($rows as $row) {
                // $row attendu : [date, label, amount]
                $amount = (string) $row['amount'];
                try {
                    $valueDate = Carbon::parse($row['date']);
                } catch (\Exception $e) {
                    // Log l'erreur, ou ajoute à une liste d'erreurs pour le rapport d'import
                    // Ou throw une exception spécifique pour arrêter l'importation
                    throw new \InvalidArgumentException('Invalid date format for row: '.json_encode($row));
                }

                BankTransaction::create([
                    'tenants_id' => $account->tenants_id,
                    'bank_account_id' => $account->id,
                    'value_date' => $valueDate,
                    'label' => $row['label'],
                    'amount' => $amount,
                    'type' => bccomp($amount, '0', 2) > 0 ? BankTransactionType::Credit : BankTransactionType::Debit,
                    'external_id' => 'MAN-'.bin2hex(random_bytes(8)),
                    'is_reconciled' => false,
                ]);

                $totalAmountAdded = bcadd($totalAmountAdded, (string) $row['amount'], 2);
                $count++;
            }

            // Mise à jour unique du solde du compte après la boucle
            $newBalance = bcadd((string) $account->current_balance, $totalAmountAdded, 2);
            $account->update(['current_balance' => $newBalance]);
            // Recharger le modèle si besoin plus tard dans la même transaction
            $account->refresh();

            return $count;
        });
    }
}
