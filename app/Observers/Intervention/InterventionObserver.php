<?php

namespace App\Observers\Intervention;

use App\Models\Intervention\Intervention;
use App\Services\Intervention\InterventionFinancialService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InterventionObserver
{
    public function __construct(
        protected InterventionFinancialService $financialService
    ) {}

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

    /**
     * Si l'intervention est modifiée (ex: changement de statut),
     * on peut déclencher des recalculs ou des audits.
     */
    public function updated(Intervention $intervention): void
    {
        // Si le statut passe à "Completed", on s'assure que la valorisation est fraîche
        if ($intervention->wasChanged('status') && $intervention->status->value === 'completed') {
            $this->financialService->refreshValuation($intervention);
        }
    }
}
