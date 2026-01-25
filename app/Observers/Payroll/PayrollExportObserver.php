<?php

namespace App\Observers\Payroll;

use App\Models\Payroll\PayrollExport;
use Illuminate\Support\Facades\Storage;

class PayrollExportObserver
{
    public function created(PayrollExport $export): void
    {
        // Log l'export
        \Log::info("Payroll export created: {$export->file_name}", [
            'uuid' => $export->uuid,
            'tenant_id' => $export->tenant_id,
            'format' => $export->format,
            'payroll_count' => $export->payroll_count,
        ]);
    }

    public function deleting(PayrollExport $export): void
    {
        // Soft delete : conserver le fichier
        if (! $export->isForceDeleting()) {
            return;
        }

        // Hard delete : supprimer le fichier physique
        if (Storage::exists($export->file_path)) {
            Storage::delete($export->file_path);
        }
    }
}
