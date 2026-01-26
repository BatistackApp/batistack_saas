<?php

namespace App\Jobs\Payroll;

use App\Models\Core\Tenant;
use App\Services\Payroll\GeneratePayrollSlipService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GeneratePayrollSlipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Tenant $tenant,
        public int $year,
        public int $month) {}

    public function handle(GeneratePayrollSlipService $service): void
    {
        try {
            $employees = $this->tenant->employees()
                ->where('is_active', true)
                ->get();

            foreach ($employees as $employee) {
                $service->generate(
                    company: $this->tenant,
                    employee: $employee,
                    year: $this->year,
                    month: $this->month,
                );
            }

            Log::info('Payroll slips generated', [
                'tenant_id' => $this->tenant->id,
                'year' => $this->year,
                'month' => $this->month,
                'count' => $employees->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Payroll generation failed', [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
