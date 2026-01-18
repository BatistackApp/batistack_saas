<?php

namespace App\Jobs\Tiers;

use App\Models\Tiers\Tiers;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncTierContactsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Tiers $tier) {}

    public function handle(): void
    {
        // Assurer qu'il y a un contact primaire
        $primaryContact = $this->tier->contacts()
            ->where('is_primary', true)
            ->first();

        if (! $primaryContact && $this->tier->contacts()->exists()) {
            $this->tier->contacts()
                ->orderBy('created_at')
                ->first()
                ->update(['is_primary' => true]);
        }

        // Vérifier que les emails primaires sont valides
        if ($primaryContact && ! filter_var($primaryContact->email, FILTER_VALIDATE_EMAIL)) {
            \Log::warning("Email invalide pour le contact primaire du tiers {$this->tier->id}");
        }
    }
}
