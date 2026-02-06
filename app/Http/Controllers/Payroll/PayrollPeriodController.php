<?php

namespace App\Http\Controllers\Payroll;

use App\Exceptions\Payroll\PayrollModuleException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Payroll\PayrollPeriodRequest;
use App\Http\Requests\Payroll\UpdatePayrollStatusRequest;
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
        protected PayrollAggregationService $aggService
    ) {}

    /**
     * Liste des périodes de paie.
     */
    public function index(): JsonResponse
    {
        $periods = PayrollPeriod::latest()->paginate();
        return response()->json($periods);
    }

    /**
     * Création d'une nouvelle période.
     */
    public function store(PayrollPeriodRequest $request): JsonResponse
    {
        $period = PayrollPeriod::create(array_merge(
            $request->validated(),
            ['tenants_id' => auth()->user()->tenants_id]
        ));

        return response()->json($period, 201);
    }

    /**
     * Génération massive des bulletins pour tous les employés actifs de la période.
     */
    public function generatePayslips(PayrollPeriod $period): JsonResponse
    {
        dd($period);
        try {
            $employees = Employee::where('is_active', true)->get();
            $count = 0;

            foreach ($employees as $employee) {
                // 1. Création ou récupération de l'en-tête du bulletin
                $payslip = Payslip::firstOrCreate([
                    'payroll_period_id' => $period->id,
                    'employee_id' => $employee->id,
                ], [
                    'tenants_id' => $period->tenants_id ?? auth()->user()->tenants_id,
                ]);

                // 2. Agrégation des données (Heures, Paniers, Trajet)
                $data = $this->aggService->getAggregatedTimeData($employee, $period);

                // 3. Calcul du moteur de paie
                $this->calcService->computePayslip($payslip, $data);
                $count++;
            }

            return response()->json(['message' => "{$count} bulletins ont été générés ou mis à jour."]);
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
}
