<?php

namespace App\Jobs\Commerce;

use App\Models\Commerce\Commande;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateCommandeNumberJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Commande $commande)
    {
    }

    public function handle(): void
    {
        $year = now()->year;
        $lastNumber = Commande::where('tenant_id', $this->commande->tenant_id)
            ->where('number', 'like', "COM-$year-%")
            ->latest('id')
            ->first();

        $sequence = $lastNumber ? (int)explode('-', $lastNumber->number)[2] + 1 : 1;
        $this->commande->update([
            'number' => sprintf('COM-%d-%06d', $year, $sequence),
        ]);
    }
}
