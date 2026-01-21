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
            ->withSum('costs', 'amount') // Calcule la somme et la stocke dans l'attribut `costs_sum_amount`
            ->chunk(100, function ($chantiers): void {
                foreach ($chantiers as $chantier) {
                    // Mettre à jour la colonne `total_costs` (après l'avoir ajoutée)
                    $chantier->updateQuietly(['total_costs' => $chantier->costs_sum_amount ?? 0]);
                }
            });
    }
}
