<?php

namespace App\Jobs\Articles;

use App\Models\Articles\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ArchiveUnusedArticleJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public function handle(): void
    {
        // Archiver les articles non utilisés depuis 1 an
        Article::whereNull('archived_at')
            ->where('updated_at', '<', now()->subYear())
            ->where('id', '!=', function ($query) {
                $query->select('article_id')
                    ->from('stock_mouvements')
                    ->where('created_at', '>', now()->subYear());
            })
            ->update(['archived_at' => now()]);
    }
}
