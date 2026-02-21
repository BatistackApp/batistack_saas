<?php

namespace App\Jobs\Locations;

use App\Enums\Locations\RentalStatus;
use App\Models\Locations\RentalContract;
use App\Services\Locations\RentalCostImputationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DailyRentalImputationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(RentalCostImputationService $imputationService): void
    {
        RentalContract::where('status', RentalStatus::ACTIVE)
            ->with(['items', 'assignments' => fn($q) => $q->whereNull('released_at')])
            ->chunk(50, function ($contracts) use ($imputationService) {
                foreach ($contracts as $contract) {
                    $imputationService->imputeDailyCost($contract);
                }
            });
    }
}
