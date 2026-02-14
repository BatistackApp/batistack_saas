<?php

namespace App\Services\Pilotage;

use App\Models\Pilotage\KpiSnapshot;
use App\Models\Pilotage\KpiThresholds;
use App\Models\User;
use Illuminate\Support\Facades\Notification;

class AlertManagerService
{
    /**
     * Compare une valeur de snapshot aux seuils définis et notifie si nécessaire.
     */
    public function checkThresholds(KpiSnapshot $snapshot): void
    {
        $thresholds = KpiThresholds::where('kpi_indicator_id', $snapshot->kpi_indicator_id)
            ->get();

        foreach ($thresholds as $threshold) {
            $isTriggered = false;
            $value = (float) $snapshot->value;

            if ($threshold->min_value !== null && $value < $threshold->min_value) {
                $isTriggered = true;
            }

            if ($threshold->max_value !== null && $value > $threshold->max_value) {
                $isTriggered = true;
            }

            if ($isTriggered && $threshold->is_notifiable) {
                $this->sendAlert($snapshot, $threshold);
            }
        }
    }

    protected function sendAlert(KpiSnapshot $snapshot, KpiThresholds $threshold): void
    {
        $indicator = $snapshot->indicator;
        $managers = User::permission('manage-kpi')->get();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new KpiThresholdAlertNotification($snapshot, $threshold));
        }
    }
}
