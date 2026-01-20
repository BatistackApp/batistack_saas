<?php

namespace App\Jobs\Articles;

use App\Models\Articles\Stock;
use App\Notifications\Articles\LowStockAlertNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckLowStockJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $lowStocks = Stock::whereColumn('quantity', '<', 'min_quantity')
            ->where('min_quantity', '>', 0)
            ->with(['article.tenant.users' => function ($query) { // Eager load nested relations
                $query->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'manager']));
            }, 'warehouse'])
            ->chunkById(100, function ($lowStocks) {
                foreach ($lowStocks as $stock) {
                    // La logique de notification ici
                    // $stock->tenant->users()
                    //    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'manager']))
                    //    ->each(fn ($user) => $user->notify(new LowStockAlertNotification($stock)));
                }
            });
    }
}
