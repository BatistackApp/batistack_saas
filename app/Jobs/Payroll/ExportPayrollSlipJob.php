<?php

namespace App\Jobs\Payroll;

use App\Enums\Payroll\PayrollExportFormat;
use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenant;
use App\Models\Payroll\PayrollSlip;
use App\Services\Payroll\PayrollExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExportPayrollSlipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Tenant $tenant,
                                public int $year,
                                public int $month,
                                public PayrollExportFormat $format) {}

    public function handle(PayrollExportService $service): void
    {
        try {
            // Récupérer les fiches validées
            $slips = $this->tenant->payrollSlips()
                ->where('year', $this->year)
                ->where('month', $this->month)
                ->where('status', PayrollStatus::Validated)
                ->get();

            if ($slips->isEmpty()) {
                Log::warning("No validated payroll slips to export", [
                    'tenant_id' => $this->tenant->id,
                    'year' => $this->year,
                    'month' => $this->month,
                ]);

                return;
            }

            // Exporter
            $export = $service->export(
                company: $this->tenant,
                year: $this->year,
                month: $this->month,
                format: $this->format,
            );

            Log::info("Payroll export completed", [
                'export_uuid' => $export->uuid,
                'payroll_count' => $export->payroll_count,
                'file_size' => $export->file_size,
            ]);
        } catch (\Exception $e) {
            Log::error("Payroll export failed", [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
