<?php

namespace App\Observers\Payroll;

use App\Jobs\Payroll\CreateAccountingEntriesJob;
use App\Jobs\Payroll\SendPayrollNotificationJob;
use App\Models\Payroll\PayrollSetting;
use App\Models\Payroll\PayrollSlip;
use App\Services\Payroll\PayrollValidationService;

class PayrollSlipObserver
{
    public function __construct(
        private PayrollValidationService $validationService,
    ) {}

    public function created(PayrollSlip $slip): void
    {
        // Auto-validate si configuré
        $setting = PayrollSetting::firstWhere('tenant_id', $slip->tenant_id);

        if ($setting?->auto_validate_payroll) {
            $slip->update(['validated_at' => now()]);
        }
    }

    public function updating(PayrollSlip $slip): void
    {
        // Revalider si en modification
        if ($slip->isDirty(['gross_amount', 'social_contributions', 'net_amount'])) {
            $validation = $this->validationService->validate($slip);

            if (! $validation['valid']) {
                $slip->validated_at = null;
            }
        }
    }

    public function updated(PayrollSlip $slip): void
    {
        // Si passage en Validated, créer écritures comptables
        if ($slip->wasChanged('validated_at') && $slip->validated_at) {
            CreateAccountingEntriesJob::dispatch($slip);

            SendPayrollNotificationJob::dispatch(
                $slip,
                'validated',
            );
        }

        // Si passage en Exported, notifier
        if ($slip->wasChanged('exported_at') && $slip->exported_at) {
            SendPayrollNotificationJob::dispatch(
                $slip,
                'exported',
            );
        }
    }

    public function deleting(PayrollSlip $slip): void
    {
        // Soft delete uniquement
        if (! $slip->isForceDeleting()) {
            return;
        }

        // Si hard delete, supprimer les écritures comptables associées
        $slip->accountingEntries()?->each(fn ($entry) => $entry->forceDelete());
    }
}
