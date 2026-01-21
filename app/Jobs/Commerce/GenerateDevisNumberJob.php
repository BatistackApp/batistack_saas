<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Devis;
use App\Services\Commerce\NumberGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateDevisNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Devis $devis)
    {
    }

    public function handle(NumberGeneratorService $numberGenerator): void
    {
        $this->devis->update([
            'number' => $numberGenerator->generateDevisNumber($this->devis->tenant_id),
        ]);
    }
}
