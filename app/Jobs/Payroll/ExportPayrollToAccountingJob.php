<?php

namespace App\Jobs\Payroll;

use App\Mail\Payroll\PayrollExportMail;
use App\Models\Payroll\PayrollPeriod;
use App\Services\Payroll\PayrollExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class ExportPayrollToAccountingJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollPeriod $period) {}

    public function handle(PayrollExportService $exportService): void
    {
        // 1. Génération du fichier physique
        $filePath = $exportService->generateAccountingExport($this->period);

        // 2. Récupération de l'email du comptable (configuré au niveau du Tenant)
        $accountantEmail = $this->period->tenant->settings->accountant_email;

        // 3. Envoi du mail
        Mail::to($accountantEmail)->send(new PayrollExportMail($this->period, $filePath));
    }
}
