<?php

namespace App\Jobs\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ValidateTierSiretJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Tiers $tiers) {}

    public function handle(): void
    {
        if (! $this->tiers->siret) {
            return;
        }

        if (! preg_match('/^\d{14}$/', $this->tiers->siret)) {
            \Log::warning("SIRET invalide pour le tiers {$this->tiers->id}: {$this->tiers->siret}");

            return;
        }

        // Vérification de la clé de contrôle SIRET
        if (! $this->isValidSiret($this->tiers->siret)) {
            \Log::warning("Clé SIRET invalide pour le tiers {$this->tiers->id}");
        }
    }

    private function isValidSiret(string $siret): bool
    {
        $sum = 0;
        for ($i = 0; $i < 14; $i++) {
            $digit = (int) $siret[$i];
            if ($i % 2 === 0) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }
        return $sum % 10 === 0;
    }
}
