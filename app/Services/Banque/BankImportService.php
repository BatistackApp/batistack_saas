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
            foreach ($rows as $row) {
                // $row attendu : [date, label, amount]
                $amount = (string) $row['amount'];

                BankTransaction::create([
                    'tenants_id' => $account->tenants_id,
                    'bank_account_id' => $account->id,
                    'value_date' => Carbon::parse($row['date']),
                    'label' => $row['label'],
                    'amount' => $amount,
                    'type' => bccomp($amount, '0', 2) > 0 ? BankTransactionType::Credit : BankTransactionType::Debit,
                    'external_id' => 'MAN-' . bin2hex(random_bytes(8)),
                    'is_reconciled' => false,
                ]);

                // Mise à jour précise du solde du compte
                $newBalance = bcadd((string)$account->current_balance, $amount, 2);
                $account->update(['current_balance' => $newBalance]);

                $count++;
            }
            return $count;
        });
    }
}
