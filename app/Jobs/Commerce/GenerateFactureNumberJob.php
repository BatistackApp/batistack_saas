<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Facture;
use App\Services\Commerce\NumberGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateFactureNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Facture $facture) {}

    public function handle(NumberGeneratorService $numberGenerator): void
    {
        $this->facture->update([
            'number' => $numberGenerator->generateFactureNumber($this->facture->tenant_id),
        ]);
    }
}
