<?php

namespace App\Services\Tiers;

use App\Models\Tiers\Tiers;

class TierCommercialTermsService
{
    public function updateCommercialTerms(Tiers $tier, array $terms): Tiers
    {
        $this->validateTerms($terms);

        $tier->update([
            'discount_percentage' => $terms['discount_percentage'] ?? $tier->discount_percentage,
            'payment_delay_days' => $terms['payment_delay_days'] ?? $tier->payment_delay_days,
            'iban' => $terms['iban'] ?? $tier->iban,
            'bic' => $terms['bic'] ?? $tier->bic,
            'vat_number' => $terms['vat_number'] ?? $tier->vat_number,
        ]);

        return $tier;
    }

    public function getCommercialTerms(Tiers $tier): array
    {
        return [
            'discount_percentage' => $tier->discount_percentage,
            'payment_delay_days' => $tier->payment_delay_days,
            'iban' => $tier->iban,
            'bic' => $tier->bic,
            'vat_number' => $tier->vat_number,
        ];
    }

    private function validateTerms(array $terms): void
    {
        if (isset($terms['discount_percentage'])) {
            if ($terms['discount_percentage'] < 0 || $terms['discount_percentage'] > 100) {
                throw new \InvalidArgumentException('La remise doit être entre 0 et 100.');
            }
        }

        if (isset($terms['payment_delay_days']) && $terms['payment_delay_days'] < 0) {
            throw new \InvalidArgumentException('Le délai de paiement ne peut pas être négatif.');
        }
    }
}
