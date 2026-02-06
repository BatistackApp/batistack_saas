<?php

namespace App\Jobs\Payroll;

use App\Enums\Payroll\PayslipLineType;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Projects\Project;
use App\Services\Projects\ProjectBudgetService;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessPayrollImputationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public PayrollPeriod $period) {}

    public function handle(): void
    {
        // On récupère toutes les lignes de type charges patronales pour la période
        $employerCosts = DB::table('payslip_lines')
            ->join('payslips', 'payslip_lines.payslip_id', '=', 'payslips.id')
            ->where('payslips.payroll_period_id', $this->period->id)
            ->where('payslip_lines.type', PayslipLineType::EmployerCost)
            ->select('payslips.employee_id', DB::raw('SUM(employer_amount) as total_employer_charges'))
            ->groupBy('payslips.employee_id')
            ->get();

        foreach ($employerCosts as $cost) {
            // Ici, on pourrait ajuster le "Actual Cost" des Projets
            // en comparant le coût chargé théorique pointé et le coût réel payé (Salaire + Charges).
            // Cette logique dépend de la structure de dénormalisation du module Project.

            $employee = Employee::find($cost->employee_id);

            if (!$employee) {
                continue;
            }

            // Calcul du coût moyen par heure travaillée
            $totalHoursWorked = DB::table('time_entries')
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$this->period->start_date, $this->period->end_date])
                ->sum('hours');

            if ($totalHoursWorked == 0) {
                continue;
            }

            $hourlyEmployerCost = $cost->total_employer_charges / $totalHoursWorked;

            // Récupération des chantiers où le salarié a travaillé
            $chantierHours = DB::table('time_entries')
                ->where('employee_id', $employee->id)
                ->whereBetween('date', [$this->period->start_date, $this->period->end_date])
                ->groupBy('project_id')
                ->select('project_id', DB::raw('SUM(hours) as total_hours'))
                ->get();

            foreach ($chantierHours as $chantierHour) {
                $imputedCost = $chantierHour->total_hours * $hourlyEmployerCost;

                // Créer ou mettre à jour l'imputation de coût réel
                DB::table('project_imputations')->updateOrInsert(
                    [
                        'project_id' => $chantierHour->project_id,
                        'employee_id' => $employee->id,
                        'payroll_period_id' => $this->period->id,
                        'type' => 'employer_costs',
                    ],
                    [
                        'amount' => $imputedCost,
                        'updated_at' => now(),
                    ]
                );

                $project = Project::find($chantierHour->project_id);
                if ($project) {
                    $budgetService = new ProjectBudgetService();
                    $budgetService->getFinancialSummary($project);
                }
            }
        }
    }
}
