<?php

namespace App\Observers\Chantiers;

use App\Models\Chantiers\Chantier;
use App\Notifications\Chantiers\ChantierBudgetAlertNotification;
use Illuminate\Support\Str;

class ChantierObserver
{
    public function creating(Chantier $chantier): void
    {
        if (empty($chantier->uuid)) {
            $chantier->uuid = (string) Str::uuid();
        }
    }

    public function updated(Chantier $chantier): void
    {
        if ($chantier->isDirty('total_costs') && $chantier->budget_usage_percent > 90) {
            $chantier->tiers?->notify(new ChantierBudgetAlertNotification($chantier));
        }
    }
}
