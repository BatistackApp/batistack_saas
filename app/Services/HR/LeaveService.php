<?php

namespace App\Services\HR;

use App\Enums\HR\LeaveStatus;
use App\Enums\HR\LeaveType;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeLeave;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class LeaveService
{
    /**
     * Créer une demande de congé
     * @throws \Exception
     */
    public function requestLeave(
        Employee $employee,
        LeaveType $leaveType,
        Carbon $startDate,
        Carbon $endDate,
        ?string $reason = null
    ): EmployeeLeave {
        if ($startDate->isAfter($endDate)) {
            throw new \Exception('La date de début doit être avant la date de fin.');
        }

        if ($this->hasConflictingLeaves($employee, $startDate, $endDate)) {
            throw new \Exception('Des congés en conflit existent déjà pour cette période.');
        }

        return $employee->leaves()->create([
            'leave_type' => $leaveType,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'status' => LeaveStatus::Pending,
            'reason' => $reason,
        ]);
    }

    /**
     * Approuver une demande de congé
     * @throws \Exception
     */
    public function approve(EmployeeLeave $leave): EmployeeLeave
    {
        if ($leave->status !== LeaveStatus::Pending) {
            throw new \Exception('Seules les demandes en attente peuvent être approuvées.');
        }

        $leave->update(['status' => LeaveStatus::Approved]);

        return $leave;
    }

    /**
     * Rejeter une demande de congé
     * @throws \Exception
     */
    public function reject(EmployeeLeave $leave, ?string $rejectionReason = null): EmployeeLeave
    {
        if ($leave->status !== LeaveStatus::Pending) {
            throw new \Exception('Seules les demandes en attente peuvent être rejetées.');
        }

        $leave->update([
            'status' => LeaveStatus::Rejected,
            'rejection_reason' => $rejectionReason,
        ]);

        return $leave;
    }

    /**
     * Vérifier si un employé a des congés approuvés pour une date donnée
     */
    public function isOnApprovedLeave(Employee $employee, Carbon $date): bool
    {
        return $employee->leaves()
            ->where('status', LeaveStatus::Approved)
            ->where('start_date', '<=', $date)
            ->where('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Vérifier les conflits de congés
     */
    public function hasConflictingLeaves(Employee $employee, Carbon $startDate, Carbon $endDate): bool
    {
        return $employee->leaves()
            ->where('status', LeaveStatus::Approved)
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate, $endDate])
                    ->orWhereBetween('end_date', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate)
                            ->where('end_date', '>=', $endDate);
                    });
            })
            ->exists();
    }

    /**
     * Récupérer les congés approuvés d'un employé pour une période
     */
    public function getApprovedLeaves(Employee $employee, Carbon $startDate, Carbon $endDate): Collection
    {
        return $employee->leaves()
            ->where('status', LeaveStatus::Approved)
            ->whereBetween('start_date', [$startDate, $endDate])
            ->orWhereBetween('end_date', [$startDate, $endDate])
            ->get();
    }

    /**
     * Calculer la durée en jours (incluant le premier et le dernier jour)
     */
    public function calculateDurationInDays(EmployeeLeave $leave): int
    {
        return $leave->end_date->diffInDays($leave->start_date) + 1;
    }
}
