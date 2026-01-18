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
            ->with(['article', 'warehouse'])
            ->get();

        foreach ($lowStocks as $stock) {
            //$stock->article->tenant->users()
            //    ->whereHas('roles', fn ($q) => $q->whereIn('name', ['admin', 'manager']))
            //    ->each(fn ($user) => $user->notify(new LowStockAlertNotification($stock)));
        }
    }
}
