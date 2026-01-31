<?php

namespace App\Jobs\Articles;

use App\Models\Articles\Article;
use App\Models\User;
use App\Notifications\Articles\LowStockAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class CheckArticleAlertLevelJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected Article $article) {}

    public function handle(): void
    {
        $totalStock = $this->article->total_stock;

        if ($totalStock <= $this->article->alert_stock && $this->article->alert_stock > 0) {

            // On récupère les utilisateurs ayant le rôle 'Achat' ou 'Logistique'
            // Dans Batistack, nous filtrons par permission ou rôle spécifique
            $recipients = User::role('logistics_manager')->get();

            if ($recipients->isNotEmpty()) {
                Notification::send($recipients, new LowStockAlertNotification($this->article, $totalStock));
            }
        }
    }
}
