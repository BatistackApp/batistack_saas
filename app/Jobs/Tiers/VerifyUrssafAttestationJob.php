<?php

namespace App\Jobs\Tiers;

use App\Models\Tiers\TierDocument;
use App\Services\UrssafApiService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class VerifyUrssafAttestationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private TierDocument $document)
    {
    }

    public function handle(UrssafApiService $api): void
    {
        if (!$this->document->relationLoaded('tier')) {
            $this->document->load('tier');
        }

        $tier = $this->document->tier;

        if (!$tier) {
            $this->fail(new \Exception('Document tier not found'));
            return;
        }

        if (!$tier->siret || !$this->document->verification_key) {
            return;
        }

        $isValid = $api->verifyAttestation($tier->siret, $this->document->verification_key);

        // Mise à jour du statut du document selon le retour de l'API
        $this->document->update([
            'status' => $isValid ? 'valid' : 'invalid_key',
            'verified_at' => $isValid ? now() : null
        ]);
    }
}
