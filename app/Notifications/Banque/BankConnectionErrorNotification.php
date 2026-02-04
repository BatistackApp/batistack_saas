<?php

namespace App\Notifications\Banque;

use App\Models\Banque\BankAccount;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BankConnectionErrorNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected BankAccount $account) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject('⚠️ Action requise : Connexion bancaire interrompue')
            ->greeting("Bonjour {$notifiable->name},")
            ->line("La synchronisation avec votre compte **{$this->account->name}** est interrompue.")
            ->line('Ceci arrive généralement lorsque votre consentement bancaire (sécurité DSP2) arrive à expiration ou que vos identifiants ont changé.')
            // ->action('Reconnecter ma banque', url('/admin/banque/accounts'))
            ->line("Batistack ne peut plus importer vos transactions automatiquement tant que la connexion n'est pas rétablie.");
    }

    public function toArray($notifiable): array
    {
        return [
            'account_id' => $this->account->id,
            'account_name' => $this->account->name,
            'bank_name' => $this->account->bank_name,
            'message' => "La connexion avec {$this->account->bank_name} nécessite une reconnexion (Consentement expiré).",
        ];
    }
}
