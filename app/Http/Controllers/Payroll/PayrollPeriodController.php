<?php

namespace App\Http\Controllers\Payroll;

use App\Enums\Payroll\PayrollStatus;
use App\Exceptions\Payroll\PayrollModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollExportRequest;
use App\Http\Requests\Payroll\PayrollPeriodRequest;
use App\Http\Requests\Payroll\UpdatePayrollStatusRequest;
use App\Jobs\Payroll\ExportPayrollToAccountingJob;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollPeriod;
use App\Models\Payroll\Payslip;
use App\Services\Payroll\PayrollAggregationService;
use App\Services\Payroll\PayrollCalculationService;
use App\Services\Payroll\PayrollWorkflowService;
use Illuminate\Http\JsonResponse;

class PayrollPeriodController extends Controller
{
    public function __construct(
        protected PayrollWorkflowService $workflowService,
        protected PayrollCalculationService $calcService,
        protected PayrollAggregationService $aggService,
    ) {}

    /**
     * Liste des périodes de paie.
     */
    public function index(): JsonResponse
    {
        $periods = PayrollPeriod::withCount('payslips')
            ->latest()
            ->paginate();

        return response()->json($periods);
    }

    /**
     * Création d'une nouvelle période.
     */
    public function store(PayrollPeriodRequest $request): JsonResponse
    {
        $period = PayrollPeriod::create([
            ...$request->validated(),
            'tenants_id' => auth()->user()->tenants_id,
            'status' => PayrollStatus::Draft,
        ]);

        return response()->json($period, 201);
    }

    /**
     * Détails d'une période spécifique.
     */
    public function show(PayrollPeriod $payrollPeriod): JsonResponse
    {
        return response()->json($payrollPeriod->loadCount('payslips'));
    }

    /**
     * Génération ou Mise à jour massive des bulletins.
     */
    public function generate(PayrollPeriod $period): JsonResponse
    {
        try {
            if ($period->status !== PayrollStatus::Draft) {
                throw new PayrollModuleException('Impossible de régénérer une période clôturée.', 422);
            }

            $employees = Employee::where('tenants_id', $period->tenants_id)
                ->where('is_active', true)
                ->get();

            $count = 0;
            foreach ($employees as $employee) {
                $payslip = Payslip::firstOrCreate([
                    'payroll_period_id' => $period->id,
                    'employee_id' => $employee->id,
                    'tenants_id' => $period->tenants_id,
                ]);

                // On récupère les pointages approuvés (Étape 4)
                $data = $this->aggService->getAggregatedData($employee, $period);

                // On calcule le moteur de paie (Étape 4)
                $this->calcService->computePayslip($payslip, $data);
                $count++;
            }

            return response()->json(['message' => "{$count} bulletins synchronisés avec les pointages."]);
        } catch (PayrollModuleException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Validation finale de la période (Clôture).
     */
    public function validatePeriod(UpdatePayrollStatusRequest $request, PayrollPeriod $period): JsonResponse
    {
        try {
            $this->workflowService->validatePeriod($period);

            return response()->json(['message' => 'Période validée et clôturée avec succès.']);
        } catch (PayrollModuleException $e) {
            return response()->json(['error' => $e->getMessage()], $e->getCode());
        }
    }

    /**
     * Export vers le comptable (Étape 5 & 6).
     */
    public function export(PayrollExportRequest $request, PayrollPeriod $period): JsonResponse
    {
        // Simulation de génération de CSV (Normalement via un Service d'export)
        ExportPayrollToAccountingJob::dispatch($period);

        return response()->json(['message' => 'L\'export a été transmis par email au cabinet comptable.']);
    }

    public function destroy(PayrollPeriod $period): JsonResponse
    {
        if ($period->status !== PayrollStatus::Draft) {
            return response()->json(['error' => 'Impossible de supprimer une période validée.'], 422);
        }

        $period->delete();

        return response()->json(['message' => 'Période supprimée.']);
    }
}
