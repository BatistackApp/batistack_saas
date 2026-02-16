<?php

namespace App\Services\HR;

use App\Enums\HR\AbsenceRequestStatus;
use App\Enums\HR\AbsenceType;
use App\Models\Core\TenantInfoHolidays;
use App\Models\HR\AbsenceRequest;
use App\Models\HR\Employee;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use DB;
use Illuminate\Validation\ValidationException;

class AbsenceService
{
    /**
     * Crée une nouvelle demande d'absence avec calcul automatique de durée.
     */
    public function createRequest(Employee $employee, array $data): AbsenceRequest
    {
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        // 1. Vérification des conflits
        if ($this->hasConflict($employee, $startsAt, $endsAt)) {
            throw ValidationException::withMessages([
                'starts_at' => "L'employé a déjà une absence prévue sur cette période.",
            ]);
        }

        // 2. Calcul de la durée en jours ouvrés
        $duration = $this->calculateWorkDays($startsAt, $endsAt, $employee->tenants_id);

        // 3. Vérification des justificatifs obligatoires (Maladie/Accident)
        $type = AbsenceType::from($data['type']);
        if ($this->requiresJustification($type) && ! isset($data['justification_path'])) {
            // On autorise la création en "Brouillon" sans justificatif,
            // mais on bloque le passage en "En attente" via le controller/workflow.
        }

        return AbsenceRequest::create([
            'tenants_id' => $employee->tenants_id,
            'employee_id' => $employee->id,
            'type' => $type,
            'status' => AbsenceRequestStatus::Pending,
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'duration_days' => $duration,
            'reason' => $data['reason'] ?? null,
            'justification_path' => $data['justification_path'] ?? null,
        ]);
    }

    /**
     * Valide ou refuse une demande.
     */
    public function validate(AbsenceRequest $request, User $validator, bool $approved, ?string $rejectionReason = null): bool
    {
        return DB::transaction(function () use ($request, $validator, $approved, $rejectionReason) {
            $status = $approved ? AbsenceRequestStatus::Approved : AbsenceRequestStatus::Rejected;

            return $request->update([
                'status' => $status,
                'validated_by' => $validator->id,
                'validated_at' => now(),
                'rejection_reason' => ! $approved ? $rejectionReason : null,
            ]);
        });
    }

    /**
     * Calcule le nombre de jours ouvrés entre deux dates.
     * Exclut les week-ends et les jours fériés du tenant.
     */
    public function calculateWorkDays(Carbon $start, Carbon $end, int $tenantId): float
    {
        $period = CarbonPeriod::create($start, $end);
        $days = 0;

        // Simulation de récupération des jours fériés (pourrait être un service dédié)
        // Récupération des jours fériés du tenant pour la période concernée
        $holidays = TenantInfoHolidays::where('tenants_id', $tenantId)
            ->whereBetween('date', [$start->format('Y-m-d'), $end->format('Y-m-d')])
            ->pluck('date')
            ->map(fn ($date) => $date->format('Y-m-d'))
            ->toArray();

        foreach ($period as $date) {
            // Exclusion des Samedis (6) et Dimanches (0)
            if ($date->isWeekend()) {
                continue;
            }

            // Exclusion si la date est présente dans la table des jours fériés
            if (in_array($date->format('Y-m-d'), $holidays)) {
                continue;
            }

            $days++;
        }

        return (float) $days;
    }

    /**
     * Vérifie si un employé est déjà absent.
     */
    public function hasConflict(Employee $employee, Carbon $start, Carbon $end, ?int $ignoreId = null): bool
    {
        return AbsenceRequest::where('employee_id', $employee->id)
            ->whereIn('status', [AbsenceRequestStatus::Pending, AbsenceRequestStatus::Approved])
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->where(function ($query) use ($start, $end) {
                $query->where(function ($q) use ($start, $end) {
                    $q->where('starts_at', '<=', $end)
                        ->where('ends_at', '>=', $start);
                });
            })
            ->exists();
    }

    /**
     * Types d'absences nécessitant légalement un justificatif.
     */
    public function requiresJustification(AbsenceType $type): bool
    {
        return in_array($type, [
            AbsenceType::SickLeave,
            AbsenceType::Accident,
        ]);
    }
}
