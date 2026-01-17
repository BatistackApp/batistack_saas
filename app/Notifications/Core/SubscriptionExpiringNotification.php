<?php

namespace App\Notifications\Core;

use App\Models\Core\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public TenantSubscription $subscription)
    {
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $planName = $this->subscription->plan_name ?? 'votre plan';

        return (new MailMessage())
            ->subject('Abonnement bientôt expiré — ' . $planName)
            ->line("L'abonnement ({$planName}) de votre organisation arrive bientôt à expiration.")
            ->line('Merci de vérifier vos informations de paiement ou de renouveler votre abonnement depuis l’espace administration.');
    }

    public function toDatabase($notifiable): array
    {
        return [];
    }

    public function toArray($notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_id' => $this->subscription->plan_id,
            'expires_at' => $this->subscription->current_period_end?->toISOString(),
        ];
    }
}
