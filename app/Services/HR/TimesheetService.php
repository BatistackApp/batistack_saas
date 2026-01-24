<?php

namespace App\Services\HR;

use App\Enums\HR\TimesheetStatus;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeTimesheet;
use App\Models\HR\EmployeeTimesheetLine;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class TimesheetService
{
    /**
     * Créer ou récupérer une feuille de pointage pour une date donnée
     */
    public function getOrCreateTimesheet(Employee $employee, Carbon $date): EmployeeTimesheet
    {
        return EmployeeTimesheet::firstOrCreate(
            [
                'employee_id' => $employee->id,
                'timesheet_date' => $date,
            ],
            [
                'status' => TimesheetStatus::Draft,
                'total_hours_work' => 0,
                'total_hours_travel' => 0,
            ]
        );
    }

    /**
     * Ajouter une ligne de pointage
     */
    public function addLine(EmployeeTimesheet $timesheet, int $chantierId, float $hoursWork, float $hoursTravel = 0, ?string $description = null): EmployeeTimesheetLine
    {
        $line = $timesheet->lines()->create([
            'chantier_id' => $chantierId,
            'hours_work' => $hoursWork,
            'hours_travel' => $hoursTravel,
            'description' => $description,
        ]);

        $this->recalculateTotals($timesheet);

        return $line;
    }

    /**
     * Mettre à jour une ligne de pointage
     */
    public function updateLine(EmployeeTimesheetLine $line, float $hoursWork, float $hoursTravel = 0, ?string $description = null): EmployeeTimesheetLine
    {
        $line->update([
            'hours_work' => $hoursWork,
            'hours_travel' => $hoursTravel,
            'description' => $description,
        ]);

        $this->recalculateTotals($line->employeeTimesheet);

        return $line;
    }

    /**
     * Supprimer une ligne de pointage
     */
    public function deleteLine(EmployeeTimesheetLine $line): void
    {
        $timesheet = $line->employeeTimesheet;
        $line->delete();

        $this->recalculateTotals($timesheet);
    }

    /**
     * Recalculer les totaux de la feuille de pointage
     */
    public function recalculateTotals(EmployeeTimesheet $timesheet): EmployeeTimesheet
    {
        $totalWork = $timesheet->lines()->sum('hours_work');
        $totalTravel = $timesheet->lines()->sum('hours_travel');

        $timesheet->update([
            'total_hours_work' => $totalWork,
            'total_hours_travel' => $totalTravel,
        ]);

        return $timesheet;
    }

    /**
     * Soumettre une feuille de pointage
     * @throws \Exception
     */
    public function submit(EmployeeTimesheet $timesheet): EmployeeTimesheet
    {
        if ($timesheet->status !== TimesheetStatus::Draft) {
            throw new \Exception('Seules les feuilles de pointage brouillon peuvent être soumises.');
        }

        $timesheet->update(['status' => TimesheetStatus::Submitted]);

        return $timesheet;
    }

    /**
     * Valider une feuille de pointage
     * @throws \Exception
     */
    public function validate(EmployeeTimesheet $timesheet): EmployeeTimesheet
    {
        if ($timesheet->status !== TimesheetStatus::Submitted) {
            throw new \Exception('Seules les feuilles soumises peuvent être validées.');
        }

        $timesheet->update(['status' => TimesheetStatus::Validated]);

        return $timesheet;
    }

    /**
     * Récupérer les feuilles de pointage validées pour une période
     */
    public function getValidatedTimesheets(Employee $employee, Carbon $startDate, Carbon $endDate): Collection
    {
        return $employee->timesheets()
            ->whereBetween('timesheet_date', [$startDate, $endDate])
            ->where('status', TimesheetStatus::Validated)
            ->get();
    }

    /**
     * Obtenir le total des heures pour une période
     */
    public function getTotalHours(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        $timesheets = $employee->timesheets()
            ->whereBetween('timesheet_date', [$startDate, $endDate])
            ->where('status', TimesheetStatus::Validated)
            ->get();

        return [
            'total_work' => $timesheets->sum('total_hours_work'),
            'total_travel' => $timesheets->sum('total_hours_travel'),
        ];
    }
}
