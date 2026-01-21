<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Situation;
use App\Services\Commerce\NumberGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateSituationNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Situation $situation)
    {
    }

    public function handle(NumberGeneratorService $numberGenerator): void
    {
        $this->situation->update([
            'number' => $numberGenerator->generateSituationNumber($this->situation->tenant_id),
        ]);
    }
}
