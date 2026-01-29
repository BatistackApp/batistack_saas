<?php

namespace App\Services\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Support\Str;

class TierCodeGenerator
{
    public function generate(): string
    {
        $prefix = Str::upper(Str::random(3));
        $count = Tiers::count() + 1;
        $number = str_pad((string) $count, 6, '0', STR_PAD_LEFT);

        return "{$prefix}-{$number}";
    }

    public function generateWithRetry(int $maxAttempts = 5): string
    {
        for ($i = 0; $i < $maxAttempts; $i++) {
            $code = $this->generate();

            if (! Tiers::where('code_tiers', $code)->exists()) {
                return $code;
            }
        }

        throw new \Exception('Unable to generate unique tier code after '.$maxAttempts.' attempts');
    }
}
