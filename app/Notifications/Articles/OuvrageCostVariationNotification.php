<?php

namespace App\Notifications\Articles;

use App\Models\Articles\Ouvrage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OuvrageCostVariationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Ouvrage $ouvrage,
        protected float $oldCost,
        protected float $newCost
    ) {}

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $diff = $this->newCost - $this->oldCost;
        $percent = ($diff / $this->oldCost) * 100;

        return (new MailMessage)
            ->error()
            ->subject("📈 Variation de coût : Ouvrage {$this->ouvrage->sku}")
            ->line("Le coût de revient de l'ouvrage **{$this->ouvrage->name}** a augmenté de ".round($percent, 2).'%.')
            ->line('Ancien coût : '.number_format($this->oldCost, 2).' € HT')
            ->line('Nouveau coût : '.number_format($this->newCost, 2).' € HT')
            ->action('Réviser les prix de vente', url("/admin/inventory/ouvrages/{$this->ouvrage->id}"))
            ->line('Cette hausse est due à la mise à jour du CUMP de l\'un de ses composants.');
    }
}
