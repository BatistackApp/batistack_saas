<?php

namespace App\Jobs\HR;

use App\Models\Chantiers\Chantier;
use App\Services\HR\ChantiersLaborCostService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GenerateLaborCostReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Chantier $chantier, private Carbon $startDate, private Carbon $endDate)
    {
    }

    public function handle(ChantiersLaborCostService $laborCostService): void
    {
        $report = $laborCostService->calculateChantieLaborCost($this->chantier, $this->startDate, $this->endDate);

        // Générer le fichier CSV/PDF et stocker
        // À compléter selon vos besoins
    }
}
