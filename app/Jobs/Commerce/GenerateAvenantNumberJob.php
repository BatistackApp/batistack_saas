<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Avenant;
use App\Services\Commerce\NumberGeneratorService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateAvenantNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private readonly Avenant $avenant)
    {
    }

    public function handle(NumberGeneratorService $numberGenerator): void
    {
        $this->avenant->update([
            'number' => $numberGenerator->generateAvenantNumber($this->avenant->tenant_id),
        ]);
    }
}
