<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Devis;
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

    public function handle(): void
    {
        $year = now()->year;
        $lastNumber = Devis::where('tenant_id', $this->devis->tenant_id)
            ->where('number', 'like', "DEV-$year-%")
            ->latest('id')
            ->first();

        $sequence = $lastNumber ? (int)explode('-', $lastNumber->number)[2] + 1 : 1;
        $this->devis->update([
            'number' => sprintf('DEV-%d-%06d', $year, $sequence),
        ]);
    }
}
