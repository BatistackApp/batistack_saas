<?php

namespace App\Observers\Intervention;

use App\Models\Intervention\Intervention;
use Illuminate\Support\Facades\Auth;
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

            $number = $latest ? ((int) Str::afterLast($latest->reference, '-') + 1) : 1;
            $intervention->reference = "INT-{$year}-" . str_pad($number, 4, '0', STR_PAD_LEFT);
        }

        if (empty($intervention->warehouse_id) && Auth::check()) {
            $employee = Auth::user()->employee; // On assume une relation employee sur User
            if ($employee && $employee->default_warehouse_id) {
                $intervention->warehouse_id = $employee->default_warehouse_id;
            }
        }
    }
}
