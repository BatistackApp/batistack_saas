<?php

namespace App\Observers\Articles;

use App\Models\Articles\Article;

class ArticleObserver
{
    public function updating(Article $article): void
    {
        if ($article->isDirty('archived_at') && !is_null($article->archived_at)) {
            // L'article est en cours d'archivage
            // Vérifier les stocks actifs
            $activeStocks = $article->stocks()
                ->where('quantity', '>', 0)
                ->count();

            if ($activeStocks > 0) {
                // Optionnel : envoyer une notification
                // Notification peut être envoyée ici si nécessaire
            }
        }
    }
}
