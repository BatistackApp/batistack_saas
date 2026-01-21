<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Commande;
use App\Services\Commerce\NumberGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCommandeNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Commande $commande)
    {
    }

    public function handle(NumberGeneratorService $numberGenerator): void
    {
        $this->commande->update([
            'number' => $numberGenerator->generateCommandeNumber($this->commande->tenant_id),
        ]);
    }
}
