<?php

namespace App\Services\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Models\Core\Tenant;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeTimesheet;
use App\Models\Payroll\PayrollSlip;
use App\Models\Payroll\PayrollSlipLine;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GeneratePayrollSlipService
{
    public function __construct(
        private CalculatePayrollService $calculateService,
        private PayrollMealAllowanceService $mealAllowanceService,
        private PayrollOvertimeService $overtimeService,
        private PayrollTravelAllowanceService $travelAllowanceService,
    ) {}

    public function generate(
        Tenant $company,
        Employee $employee,
        int $year,
        int $month,
    ): PayrollSlip {
        $periodStart = Carbon::createFromDate($year, $month, 1);
        $periodEnd = $periodStart->clone()->endOfMonth();

        // Vérifier pas de doublon
        $existingSlip = PayrollSlip::query()
            ->where('tenant_id', $company->id)
            ->where('employee_id', $employee->id)
            ->where('year', $year)
            ->where('month', $month)
            ->first();

        if ($existingSlip) {
            return $existingSlip;
        }

        // Récupérer les entrées de pointage
        $timesheets = $this->getTimesheetEntries(
            $employee,
            $periodStart,
            $periodEnd,
        );

        // Calculer les montants
        $calculations = $this->calculateService->calculate(
            $employee,
            $company,
            $timesheets,
        );

        // Calculs des indemnités et majorations
        $mealAllowanceData = $this->mealAllowanceService->calculateForPayrollSlip(
            PayrollSlip::make([
                'employee_id' => $employee->id,
                'tenant_id' => $company->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ])
        );

        $overtimeData = $this->overtimeService->calculateForPayrollSlip(
            PayrollSlip::make([
                'employee_id' => $employee->id,
                'tenant_id' => $company->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ]),
            $timesheets,
        );

        $travelAllowanceData = $this->travelAllowanceService->calculateForPayrollSlip(
            PayrollSlip::make([
                'employee_id' => $employee->id,
                'tenant_id' => $company->id,
                'period_start' => $periodStart,
                'period_end' => $periodEnd,
            ])
        );

        // Calcul du montant brut total
        $grossAmount = $calculations['gross_amount']
            + $mealAllowanceData['total_amount']
            + $overtimeData['total_amount']
            + $travelAllowanceData['total_amount'];

        // Créer la fiche
        $slip = PayrollSlip::create([
            'uuid' => Str::uuid(),
            'tenant_id' => $company->id,
            'employee_id' => $employee->id,
            'year' => $year,
            'month' => $month,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'status' => PayrollStatus::Draft,
            'total_hours_work' => $calculations['total_hours_work'],
            'total_hours_travel' => $calculations['total_hours_travel'],
            'gross_amount' => $grossAmount,
            'social_contributions' => $calculations['social_contributions'],
            'employee_deductions' => $calculations['employee_deductions'],
            'net_amount' => $calculations['net_amount'],
            'transport_amount' => $calculations['transport_amount'],
        ]);

        // Créer les lignes par chantier
        $this->createPayrollLines($slip, $calculations['lines']);

        // Créer les lignes d'indemnités
        $this->mealAllowanceService->createMealAllowanceLines($slip, $mealAllowanceData);
        $this->overtimeService->createOvertimeLines($slip, $overtimeData);
        $this->travelAllowanceService->createTravelAllowanceLines($slip, $travelAllowanceData);

        return $slip;
    }

    /**
     * @return Collection<EmployeeTimesheet>
     */
    private function getTimesheetEntries(
        Employee $employee,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): Collection {
        return EmployeeTimesheet::query()
            ->where('employee_id', $employee->id)
            ->whereBetween('timesheet_date', [$periodStart, $periodEnd])
            ->with('chantier')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $lines
     */
    private function createPayrollLines(PayrollSlip $slip, array $lines): void
    {
        foreach ($lines as $line) {
            PayrollSlipLine::create([
                'payroll_slip_id' => $slip->id,
                'chantier_id' => $line['chantier_id'],
                'description' => $line['description'],
                'hours_work' => $line['hours_work'],
                'hours_travel' => $line['hours_travel'],
                'hourly_rate' => $line['hourly_rate'],
                'amount' => $line['amount'],
            ]);
        }
    }
}
