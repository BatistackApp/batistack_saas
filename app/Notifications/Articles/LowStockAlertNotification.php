<?php

namespace App\Notifications\Articles;

use App\Models\Articles\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Article $article,
        protected float $currentStock,
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->error()
            ->subject("⚠️ Alerte Stock : Rupture imminente sur {$this->article->sku}")
            ->line("L'article **{$this->article->name}** ({$this->article->sku}) a atteint son seuil d'alerte.")
            ->line("Stock actuel : **{$this->currentStock} {$this->article->unit->value}**.")
            ->line("Seuil d'alerte configuré : {$this->article->alert_stock}.")
            // ->action('Gérer le réapprovisionnement', url("/admin/articles/{$this->article->id}"))
            ->line('Il est recommandé de passer commande auprès du fournisseur : '.($this->article->supplier->name ?? 'Non défini'));
    }

    public function toArray($notifiable): array
    {
        return [
            'article_id' => $this->article->id,
            'sku' => $this->article->sku,
            'current_stock' => $this->currentStock,
            'message' => "Seuil d'alerte atteint pour {$this->article->sku}",
        ];
    }
}
