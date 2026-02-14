<?php

namespace App\Enums\Accounting;

enum JournalType: string
{
    case SalesJournal = 'VE'; // Ventes
    case PurchasesJournal = 'AC'; // Achats
    case BankJournal = 'BQ'; // Banque
    case PayrollJournal = 'PA'; // Paie
    case MiscellaneousJournal = 'OD'; // Opérations Diverses
}
