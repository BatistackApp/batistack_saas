<?php

namespace App\Observers\Payroll;

use App\Models\Payroll\PayrollSetting;

class PayrollSettingObserver
{
    public function saving(PayrollSetting $setting): void
    {
        // Valider le taux de cotisation
        if ($setting->social_contribution_rate < 0 || $setting->social_contribution_rate > 100) {
            throw new \InvalidArgumentException(
                'Le taux de cotisation doit être entre 0 et 100%'
            );
        }
    }

    public function updated(PayrollSetting $setting): void
    {
        // Log les modifications importantes
        if ($setting->wasChanged('social_contribution_rate')) {
            \Log::info("Payroll contribution rate updated", [
                'tenant_id' => $setting->tenant_id,
                'old' => $setting->getOriginal('social_contribution_rate'),
                'new' => $setting->social_contribution_rate,
            ]);
        }
    }
}
