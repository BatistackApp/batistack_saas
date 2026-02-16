<?php

namespace App\Services\HR;

use App\Enums\HR\SkillType;
use App\Models\HR\Employee;
use App\Models\HR\EmployeeSkill;
use App\Models\HR\Skill;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SkillManagerService
{
    /**
     * Assigne ou met à jour une compétence pour un employé.
     */
    public function assignSkill(Employee $employee, Skill $skill, array $data): EmployeeSkill|Model
    {
        return EmployeeSkill::updateOrCreate(
            [
                'employee_id' => $employee->id,
                'skill_id' => $skill->id,
            ],
            [
                'issue_date' => $data['issue_date'] ?? null,
                'expiry_date' => $data['expiry_date'] ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'level' => $data['level'] ?? 1,
                'notes' => $data['notes'] ?? null,
                'document_path' => $data['document_path'] ?? null,
            ]
        );
    }

    /**
     * Récupère la liste des habilitations expirant prochainement pour un tenant.
     */
    public function getExpiringSkills(int $tenantId, int $days = 30): Collection
    {
        return EmployeeSkill::query()
            ->whereHas('employee', function ($query) use ($tenantId) {
                $query->where('tenants_id', $tenantId);
            })
            ->whereNotNull('expiry_date')
            ->whereBetween('expiry_date', [now(), now()->addDays($days)])
            ->with(['employee', 'skill'])
            ->get();
    }

    /**
     * Analyse le taux de conformité d'un employé.
     * Vérifie si toutes les compétences de type 'Certification', 'Habilitation' et 'Medical'
     * possédées par l'employé sont encore valides.
     */
    public function getEmployeeComplianceStatus(Employee $employee): array
    {
        $skills = $employee->skills()->with('skill')->get();

        $totalMandatory = $skills->filter(fn ($es) => in_array($es->skill->type, [
            SkillType::Habilitation,
            SkillType::Certification,
            SkillType::Medical,
        ]))->count();

        $expiredCount = $skills->filter(fn ($es) => $es->isExpired())->count();
        $expiringSoonCount = $skills->filter(fn ($es) => $es->expiresSoon())->count();

        return [
            'is_compliant' => $expiredCount === 0,
            'total_skills' => $skills->count(),
            'expired_count' => $expiredCount,
            'expiring_soon' => $expiringSoonCount,
            'score' => $totalMandatory > 0 ? (($totalMandatory - $expiredCount) / $totalMandatory) * 100 : 100,
        ];
    }

    /**
     * Génère une matrice de compétences pour un département ou un projet.
     * Utile pour visualiser qui sait faire quoi d'un coup d'œil.
     */
    public function getCompetencyMatrix(int $tenantId, ?string $department = null): Collection
    {
        return Employee::query()
            ->where('tenants_id', $tenantId)
            ->when($department, fn ($q) => $q->where('department', $department))
            ->with(['skills.skill'])
            ->get()
            ->map(function ($employee) {
                return [
                    'employee_id' => $employee->id,
                    'full_name' => $employee->full_name,
                    'skills' => $employee->skills->mapWithKeys(function ($es) {
                        return [$es->skill->name => [
                            'level' => $es->level,
                            'is_valid' => ! $es->isExpired(),
                            'type' => $es->skill->type->value,
                            'expiry_date' => $es->expiry_date?->format('d/m/Y'),
                        ]];
                    }),
                ];
            });
    }
}
