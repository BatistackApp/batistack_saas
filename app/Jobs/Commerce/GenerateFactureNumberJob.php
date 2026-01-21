<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Facture;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateFactureNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Facture $facture) {}

    public function handle(): void
    {
        $year = now()->year;
        $lastNumber = Facture::where('tenant_id', $this->facture->tenant_id)
            ->where('number', 'like', "FAC-$year-%")
            ->latest('id')
            ->first();

        $sequence = $lastNumber ? (int) explode('-', $lastNumber->number)[2] + 1 : 1;
        $this->facture->update([
            'number' => sprintf('FAC-%d-%06d', $year, $sequence),
        ]);
    }
}
