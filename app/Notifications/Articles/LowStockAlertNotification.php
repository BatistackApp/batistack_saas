<?php

namespace App\Notifications\Articles;

use App\Models\Articles\Stock;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockAlertNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly Stock $stock) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'article_id' => $this->stock->article_id,
            'article_name' => $this->stock->article->name,
            'warehouse_id' => $this->stock->warehouse_id,
            'current_quantity' => $this->stock->quantity,
            'min_quantity' => $this->stock->min_quantity,
            'warehouse_name' => $this->stock->warehouse->name,
        ];
    }
}
