<?php

namespace App\Notifications\HR;

use App\Models\HR\AbsenceRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AbsenceRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected AbsenceRequest $request,
        protected string $type // 'submitted' ou 'status_changed'
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $statusLabel = $this->request->status->getLabel();
        $employeeName = $this->request->employee->full_name;

        if ($this->type === 'submitted') {
            return (new MailMessage)
                ->subject("Nouvelle demande d'absence : $employeeName")
                ->line("$employeeName a déposé une demande du {$this->request->starts_at->format('d/m/Y')} au {$this->request->ends_at->format('d/m/Y')}.")
                ->action('Voir la demande', url('/admin/absence-requests/'.$this->request->id));
        }

        return (new MailMessage)
            ->subject("Mise à jour de votre demande d'absence")
            ->line("Votre demande pour la période du {$this->request->starts_at->format('d/m/Y')} est désormais : $statusLabel")
            ->when($this->request->rejection_reason, function ($mail) {
                return $mail->line('Motif : '.$this->request->rejection_reason);
            });
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'absence_request_id' => $this->request->id,
            'message' => $this->type === 'submitted'
                ? "Nouvelle demande de {$this->request->employee->full_name}"
                : "Votre demande d'absence est ".$this->request->status->value,
        ];
    }
}
