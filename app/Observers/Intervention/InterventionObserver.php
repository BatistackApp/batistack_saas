<?php

namespace App\Observers\Intervention;

use App\Models\Intervention\Intervention;
use Illuminate\Support\Str;

class InterventionObserver
{
    public function creating(Intervention $intervention): void
    {
        if (empty($intervention->reference)) {
            $year = now()->format('Y');
            $latest = Intervention::where('reference', 'LIKE', "INT-{$year}-%")
                ->latest('id')
                ->first();

            if ($latest) {
                $number = (int) Str::afterLast($latest->reference, '-') + 1;
            } else {
                $number = 1;
            }

            $intervention->reference = "INT-{$year}-".str_pad($number, 4, '0', STR_PAD_LEFT);
        }
    }
}
