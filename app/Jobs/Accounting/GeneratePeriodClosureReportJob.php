<?php

namespace App\Jobs\Accounting;

use App\Models\Accounting\PeriodClosure;
use App\Models\User;
use App\Notifications\Accounting\PeriodClosedNotification;
use App\Services\Accounting\PeriodClosureService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class GeneratePeriodClosureReportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private PeriodClosure $closure,
    ) {}

    public function handle(PeriodClosureService $service): void
    {
        $report = $service->generateClosureReport(
            $this->closure->month,
            $this->closure->year
        );

        // Sauvegarder le rapport (ex: en JSON dans le storage ou DB)
        \Illuminate\Support\Facades\Storage::disk('public')->put(
            "tenants/{$this->closure->tenants_id}/accounting/closure_reports/{$this->closure->year}/{$this->closure->month}/report.json",
            json_encode($report, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        // Notifier que le rapport est prêt
        $this->closure->closedByUsed->notify(new PeriodClosedNotification($this->closure));
    }
}
