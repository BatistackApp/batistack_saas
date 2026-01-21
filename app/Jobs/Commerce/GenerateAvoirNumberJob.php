<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Avoir;
use App\Services\Commerce\NumberGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAvoirNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Avoir $avoir)
    {
    }

    public function handle(NumberGeneratorService $numberGenerator): void
    {
        $this->avoir->update([
            'number' => $numberGenerator->generateAvoirNumber($this->avoir->tenant_id),
        ]);
    }
}
