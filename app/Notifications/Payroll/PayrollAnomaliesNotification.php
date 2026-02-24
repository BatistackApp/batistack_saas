<?php

namespace App\Notifications\Payroll;

use App\Models\Payroll\PayrollPeriod;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PayrollAnomaliesNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public PayrollPeriod $period,
        public array $anomalies
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $email = (new MailMessage)
            ->subject("Anomalies détectées - Paie {$this->period->name}")
            ->line("Le contrôle automatique a détecté des points d'attention avant la clôture de la paie.");

        foreach ($this->anomalies as $anomaly) {
            $email->line("- " . $anomaly);
        }

        return $email->action('Vérifier la période', url('/payroll/periods/' . $this->period->id))
            ->line('Merci de corriger ces données pour garantir une paie exacte.');
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'period_id' => $this->period->id,
            'anomalies_count' => count($this->anomalies),
            'message' => count($this->anomalies) . " anomalies détectées sur la paie " . $this->period->name,
        ];
    }
}
