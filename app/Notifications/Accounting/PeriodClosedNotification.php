<?php

namespace App\Notifications\Accounting;

use App\Models\Accounting\PeriodClosure;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PeriodClosedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private PeriodClosure $closure,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Clôture de période : {$this->closure->month}/{$this->closure->year}")
            ->greeting("Bonjour,")
            ->line("La période comptable {$this->closure->month}/{$this->closure->year} a été clôturée.")
            ->line("Aucune modification ne sera possible après cette date.")
            // ->action('Voir les détails', route('accounting.period-closures.show', $this->closure))
            ->line('Merci d\'utiliser Batistack.');
    }
    public function toArray($notifiable): array
    {
        return [
            'title' => "Clôture de période {$this->closure->month}/{$this->closure->year}",
            'message' => "La période a été clôturée par {$this->closure->closedByUser->name}",
            'period_closure_id' => $this->closure->id,
        ];
    }
}
