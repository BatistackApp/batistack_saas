<?php

namespace App\Traits;

use App\Enums\Payroll\PayrollStatus;
use App\Models\Payroll\PayrollPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

trait PayrollLockObserverTrait
{
    /**
     * Vérifie si la date donnée appartient à une période de paie clôturée.
     * * @throws ValidationException
     */
    protected function checkPayrollLock(int $tenantId, $date): void
    {
        $carbonDate = Carbon::parse($date);

        $isLocked = PayrollPeriod::where('tenants_id', $tenantId)
            ->where('start_date', '<=', $carbonDate->format('Y-m-d'))
            ->where('end_date', '>=', $carbonDate->format('Y-m-d'))
            ->whereIn('status', [PayrollStatus::Validated, PayrollStatus::Paid])
            ->exists();

        if ($isLocked) {
            throw ValidationException::withMessages([
                'payroll' => "Action impossible : Cette date appartient à une période de paie déjà clôturée ou en cours de virement."
            ]);
        }
    }
}
