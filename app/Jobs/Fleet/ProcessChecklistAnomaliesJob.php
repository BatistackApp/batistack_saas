<?php

namespace App\Jobs\Fleet;

use App\Enums\Fleet\MaintenanceStatus;
use App\Enums\Fleet\MaintenanceType;
use App\Models\Fleet\VehicleCheck;
use App\Models\Fleet\VehicleMaintenance;
use App\Notifications\Fleet\ChecklistAnomalyNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class ProcessChecklistAnomaliesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public VehicleCheck $check
    ) {}

    public function handle(): void
    {
        // 1. Récupération des détails des anomalies
        $anomalies = $this->check->results()
            ->where('is_anomaly', true)
            ->with('question')
            ->get();

        if ($anomalies->isEmpty()) {
            return;
        }

        // 2. Construction du rapport de panne
        $report = "🚨 Anomalies signalées lors du contrôle ({$this->check->type}) :\n";
        foreach ($anomalies as $res) {
            $report .= "- {$res->question->label} : ".($res->anomaly_description ?: 'Pas de commentaire')."\n";
        }

        // 3. Création automatique d'une maintenance curative
        $maintenance = VehicleMaintenance::create([
            'tenants_id' => $this->check->tenants_id,
            'vehicle_id' => $this->check->vehicle_id,
            'reported_by' => $this->check->user_id,
            'internal_reference' => 'AUTO-'.strtoupper(Str::random(6)),
            'maintenance_type' => MaintenanceType::Curative,
            'maintenance_status' => MaintenanceStatus::Reported,
            'description' => $report,
            'reported_at' => now(),
            'odometer_reading' => $this->check->odometer_reading,
        ]);

        // 4. Notification des gestionnaires de flotte du tenant
        // On récupère les utilisateurs ayant la permission de gérer la flotte pour ce tenant
        $managers = \App\Models\User::where('tenants_id', $this->check->tenants_id)
            ->permission('fleet.manage')
            ->get();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new ChecklistAnomalyNotification($this->check, $maintenance));
        }
    }
}
