<?php

namespace App\Jobs\Expense;

use App\Models\Expense\ExpenseReport;
use App\Services\Expense\ChantierImputationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessChantierImputationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public ExpenseReport $report
    ) {}

    public function handle(ChantierImputationService $imputationService): void
    {
        $imputationService->imputeReportToChantiers($this->report);
    }
}
