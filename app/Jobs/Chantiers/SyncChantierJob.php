<?php

namespace App\Jobs\Chantiers;

use App\Models\Chantiers\Chantier;
use App\Services\Chantiers\ChantierService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncChantierJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly ChantierService $chantierService) {}

    public function handle(): void
    {
        Chantier::query()
            ->with('costs')
            ->chunk(100, function ($chantiers): void {
                foreach ($chantiers as $chantier) {
                    $this->chantierService->recalculateTotalCosts($chantier);
                }
            });
    }
}
