<?php

namespace App\Enums\Banque;

enum BankTransactionType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
}
