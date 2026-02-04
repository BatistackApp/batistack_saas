<?php

namespace App\Observers;

use App\Jobs\Banque\SyncBankTransactionsJob;
use App\Models\Banque\BankAccount;

class BankAccountObserver
{
    public function updated(BankAccount $account): void
    {
        // On déclenche le job si le bridge_id vient d'être renseigné (première connexion)
        if ($account->wasChanged('bridge_id') && $account->bridge_id) {
            SyncBankTransactionsJob::dispatch($account);
        }
    }
}
