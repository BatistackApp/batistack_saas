<?php

namespace App\Notifications\Core;

use App\Models\Core\TenantSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionActivatedNotification extends Notification implements ShouldQueue
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
            ->subject('Abonnement activé — ' . $planName)
            ->line("L'abonnement ({$planName}) de votre organisation est désormais actif.");
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
            'status' => $this->subscription->status->getLabel(),
        ];
    }
}
