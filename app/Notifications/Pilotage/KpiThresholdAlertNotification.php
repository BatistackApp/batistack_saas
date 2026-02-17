<?php

namespace App\Notifications\Pilotage;

use App\Models\Pilotage\KpiSnapshot;
use App\Models\Pilotage\KpiThresholds;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KpiThresholdAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public KpiSnapshot $snapshot,
        public KpiThresholds $threshold
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $indicator = $this->snapshot->indicator;
        $severity = strtoupper($this->threshold->severity->value);
        $valueFormatted = number_format((float) $this->snapshot->value, 2);

        return (new MailMessage)
            ->error()
            ->subject("[$severity] Alerte Performance Batistack : {$indicator->name}")
            ->greeting('Bonjour '.$notifiable->name)
            ->line("Un indicateur de performance a franchi un seuil d'alerte sur votre espace.")
            ->line("Indicateur : **{$indicator->name}**")
            ->line("Valeur mesurée : **{$valueFormatted} {$indicator->unit->value}**")
            ->line('Seuil critique : '.($this->threshold->min_value ?? 'N/A').' - '.($this->threshold->max_value ?? 'N/A'))
            // ->action('Analyser sur le Dashboard', url('/admin/pilotage/dashboards'))
            ->line('Une action corrective est peut-être nécessaire sur le chantier ou la trésorerie.');
    }

    public function toArray($notifiable): array
    {
        return [
            'indicator_id' => $this->snapshot->kpi_indicator_id,
            'indicator_name' => $this->snapshot->indicator->name,
            'value' => $this->snapshot->value,
            'severity' => $this->threshold->severity->value,
            'message' => "Alerte de seuil {$this->threshold->severity->value} sur {$this->snapshot->indicator->name}.",
        ];
    }
}
