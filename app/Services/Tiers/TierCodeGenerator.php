<?php

namespace App\Services\Tiers;

use App\Enums\Tiers\TierType;
use App\Models\Tiers\Tiers;

class TierCodeGenerator
{
    public function generate(TierType $type): string
    {
        $prefix = match ($type) {
            TierType::Customer => 'CLI',
            TierType::Supplier => 'SUP',
            TierType::Subcontractor => 'SUB',
            TierType::Employee => 'EMP',
        };
        // Utilise une partie du timestamp et une chaîne aléatoire pour plus d'unicité
        $number = substr(time(), -3).str_pad(random_int(0, 999), 3, '0', STR_PAD_LEFT);

        return "{$prefix}-{$number}";
    }

    public function generateWithRetry(TierType $type, int $maxAttempts = 5): string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = $this->generate($type);

            if (! Tiers::where('code_tiers', $code)->exists()) {
                return $code;
            }
        }

        throw new \Exception('Unable to generate unique tier code after '.$maxAttempts.' attempts');
    }
}
