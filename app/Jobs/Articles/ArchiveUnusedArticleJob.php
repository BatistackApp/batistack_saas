<?php

namespace App\Jobs\Articles;

use App\Models\Articles\Article;
use App\Models\Core\Tenant;
use DB;
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
        $tenants = Tenant::all();
        foreach ($tenants as $tenant) {
            // Archiver les articles non utilisés depuis 1 an
            Article::where('tenant_id', $tenant->id)
                ->whereNull('archived_at')
                ->where('updated_at', '<', now()->subYear())
                ->whereNotExists(function ($query) {
                    $query->select(DB::raw(1))
                        ->from('stock_mouvements')
                        ->whereColumn('stock_mouvements.article_id', 'articles.id')
                        ->where('created_at', '>', now()->subYear());
                })
                ->update(['archived_at' => now()]);
        }
    }
}
