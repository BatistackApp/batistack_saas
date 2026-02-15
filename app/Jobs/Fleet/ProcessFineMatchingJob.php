<?php

namespace App\Jobs\Fleet;

use App\Models\Fleet\VehicleFine;
use App\Services\Fleet\FineService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class ProcessFineMatchingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(protected VehicleFine $fine) {}

    public function handle(FineService $fineService): void
    {
        try {
            $result = $fineService->autoReconcile($this->fine);

            if ($result['match_found']) {
                Log::info("Contravention #{$this->fine->notice_number} rapprochée avec succès.");
            }
        } catch (\Exception $e) {
            Log::error("Erreur lors du matching de l'amende {$this->fine->id}: " . $e->getMessage());
        }
    }
}
