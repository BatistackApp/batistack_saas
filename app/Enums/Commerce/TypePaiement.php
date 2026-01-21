<?php

namespace App\Enums\Commerce;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Contracts\Support\Htmlable;

enum TypePaiement: string implements HasLabel
{
    case ESPECE = 'espece';
    case CHEQUE = 'cheque';
    case VIREMENT = 'virement';
    case CARTE_BANCAIRE = 'carte_bancaire';
    case PAYPAL = 'paypal';
    case AUTRE = 'autre';

    public function getLabel(): string|Htmlable|null
    {
        return match ($this) {
            self::ESPECE => __('commerce.type_paiement.espece'),
            self::CHEQUE => __('commerce.type_paiement.cheque'),
            self::VIREMENT => __('commerce.type_paiement.virement'),
            self::CARTE_BANCAIRE => __('commerce.type_paiement.carte_bancaire'),
            self::PAYPAL => __('commerce.type_paiement.paypal'),
            self::AUTRE => __('commerce.type_paiement.autre'),
        };
    }
}
